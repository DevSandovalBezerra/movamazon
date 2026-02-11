<?php
// Headers CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Responder OPTIONS para preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../db.php';
require_once '../helpers/inscricao_logger.php';
require_once '../mercadolivre/MercadoPagoClient.php';

// Configurações do Mercado Pago
$config = require '../mercadolivre/config.php';
$access_token = $config['accesstoken'];

// Validação crítica: verificar se token está configurado
if (empty($access_token)) {
    $env = $config['environment'] ?? 'desconhecido';
    error_log("ERRO CREATE_PREFERENCE: Access token não configurado para ambiente '$env'");
    logInscricaoPagamento('ERROR', 'TOKEN_NAO_CONFIGURADO', [
        'ambiente' => $env,
        'erro' => 'Access token não configurado'
    ]);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => "Configuração de pagamento inválida. Verifique APP_Acess_token e APP_Public_Key no .env"
    ]);
    exit;
}

try {
    // Validar dados de entrada
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Dados não fornecidos');
    }

    // Validar campos obrigatórios
    $required_fields = ['inscricao_id', 'valor_total', 'evento_nome'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            throw new Exception("Campo obrigatório não fornecido: $field");
        }
    }

    $inscricao_id = (int)$input['inscricao_id'];
    $valor_total = (float)$input['valor_total'];
    $evento_nome = $input['evento_nome'];
    $modalidade_nome = $input['modalidade_nome'] ?? 'Inscrição';
    $produtos_extras = $input['produtos_extras'] ?? [];
    // Checkout Pro: origem define para onde o MP redireciona (rollback: remover e usar $config['back_urls'])
    $origem = isset($input['origem']) && in_array($input['origem'], ['inscricao', 'participante'], true)
        ? $input['origem'] : 'inscricao';
    $back_urls = isset($config['back_urls_' . $origem]) ? $config['back_urls_' . $origem] : $config['back_urls'];

    // Validar valor
    if ($valor_total <= 0) {
        throw new Exception('Valor total deve ser maior que zero');
    }

    // Log de auditoria
    if (function_exists('logMercadoPago')) {
        logMercadoPago('preference', 'Criando preference', [
            'inscricao_id' => $inscricao_id,
            'valor_total' => $valor_total,
            'environment' => $config['environment']
        ]);
    }

    // Buscar dados da inscrição no banco
    $stmt = $pdo->prepare("SELECT *, external_reference FROM inscricoes WHERE id = ?");
    $stmt->execute([$inscricao_id]);
    $inscricao = $stmt->fetch();

    if (!$inscricao) {
        throw new Exception('Inscrição não encontrada');
    }

    // ✅ Usar external_reference da inscrição se existir, senão usar o ID
    $external_reference = $inscricao['external_reference'] ?: 'MOVAMAZON_' . $inscricao_id;

    // Buscar dados do usuário
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$inscricao['usuario_id']]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        throw new Exception('Usuário não encontrado');
    }

    // Montar descrição dos itens
    $items = [];

    // Imagem do item: usar config (logo MovAmazon) para aparecer no Checkout Pro
    $picture_url = $config['item_defaults']['picture_url'] ?? 'https://www.movamazon.com.br/frontend/assets/img/logo.png';

    // Item principal - Modalidade (items.id exigência MP)
    $items[] = [
        'id' => 'MOVAMAZON_' . $inscricao_id,
        'title' => $modalidade_nome,
        'description' => "Inscrição no evento: $evento_nome",
        'picture_url' => $picture_url,
        'category_id' => 'EBL-Evento Desportivo',
        'quantity' => 1,
        'currency_id' => 'BRL',
        'unit_price' => $valor_total
    ];

    // Adicionar produtos extras se houver
    $idx_extra = 0;
    foreach ($produtos_extras as $produto) {
        if (isset($produto['nome']) && isset($produto['valor']) && $produto['valor'] > 0) {
            $items[] = [
                'id' => 'extra_' . $inscricao_id . '_' . $idx_extra,
                'title' => $produto['nome'],
                'description' => $produto['descricao'] ?? 'Produto adicional',
                'picture_url' => $picture_url,
                'category_id' => 'EBL-Evento Desportivo',
                'quantity' => 1,
                'currency_id' => 'BRL',
                'unit_price' => (float)$produto['valor']
            ];
            $idx_extra++;
        }
    }

    // Calcular total dos itens
    $total_items = array_sum(array_column($items, 'unit_price'));

    // Se há diferença entre valor total e soma dos itens, ajustar o primeiro item
    if (abs($total_items - $valor_total) > 0.01) {
        $items[0]['unit_price'] = $valor_total - ($total_items - $items[0]['unit_price']);
    }

    // statement_descriptor: nome na fatura do cartão (exigência MP - reduz contestações)
    $statement_descriptor = $config['statement_descriptor'] ?? 'MOVAMAZON';

    // additional_info: texto exibido no resumo do Checkout Pro (reforça evento)
    $additional_info = "Evento: $evento_nome. Inscrição MovAmazon.";

    // Montar dados da preference - back_urls por origem (inscricao | participante)
    $preference_data = [
        'back_urls' => $back_urls,
        'external_reference' => $external_reference,
        'notification_url' => $config['url_notification_api'],
        'auto_return' => 'approved',
        'statement_descriptor' => $statement_descriptor,
        'additional_info' => $additional_info,
        'items' => $items,
        'payer' => [
            'name' => $usuario['nome_completo'] ?? $usuario['nome'],
            'email' => $usuario['email'],
            'identification' => [
                'type' => 'CPF',
                'number' => $usuario['documento']
            ]
        ],
        'payment_methods' => [
            'excluded_payment_methods' => [
                // Excluir apenas Mastercard se necessário
                // ['id' => 'master']
            ],
            'excluded_payment_types' => [],
            'installments' => 12 // Máximo de parcelas
        ],
        'metadata' => [
            'inscricao_id' => $inscricao_id,
            'evento_nome' => $evento_nome,
            'modalidade_nome' => $modalidade_nome,
            'usuario_id' => $usuario['id']
        ]
    ];

    // Criar preference via MercadoPagoClient (SDK ou cURL)
    $client = new MercadoPagoClient($config);
    $preference = $client->createPreference($preference_data);

    if (!isset($preference['id'])) {
        error_log("ERRO: Resposta do Mercado Pago sem ID: " . json_encode($preference));
        
        logInscricaoPagamento('ERROR', 'RESPOSTA_INVALIDA_PREFERENCE', [
            'inscricao_id' => $inscricao_id,
            'erro' => 'Resposta do Mercado Pago sem ID',
            'resposta' => substr(json_encode($preference), 0, 500)
        ]);
        
        if (function_exists('logMercadoPago')) {
            logMercadoPago('error', 'Resposta inválida do Mercado Pago', [
                'inscricao_id' => $inscricao_id,
                'response' => substr(json_encode($preference), 0, 500),
                'environment' => $config['environment'] ?? 'desconhecido'
            ]);
        }
        
        throw new Exception('Erro ao criar preference: resposta inválida do Mercado Pago');
    }

    // Salvar preference_id na inscrição
    try {
        $stmt = $pdo->prepare("UPDATE inscricoes SET preference_id = ? WHERE id = ?");
        $stmt->execute([$preference['id'], $inscricao_id]);

        if ($stmt->rowCount() === 0) {
            error_log("⚠️ Nenhuma linha foi atualizada para inscrição ID: $inscricao_id");
        }
    } catch (PDOException $e) {
        error_log("❌ Erro ao salvar preference_id: " . $e->getMessage());
        throw new Exception("Erro ao salvar preference_id: " . $e->getMessage());
    }

    // Log de auditoria
    logInscricaoPagamento('SUCCESS', 'PREFERENCE_CRIADA', [
        'inscricao_id' => $inscricao_id,
        'preference_id' => $preference['id'],
        'valor_total' => $valor_total,
        'init_point' => $preference['init_point'] ?? null
    ]);
    
    if (function_exists('logMercadoPago')) {
        logMercadoPago('preference', 'Preference criada com sucesso', [
            'preference_id' => $preference['id'],
            'inscricao_id' => $inscricao_id,
            'environment' => $config['environment']
        ]);
    }

    // Retornar resposta
    echo json_encode([
        'success' => true,
        'preference_id' => $preference['id'],
        'init_point' => $preference['init_point'] ?? '',
        'total_amount' => $valor_total,
        'external_reference' => $external_reference,
        'items_count' => count($items)
    ]);
} catch (Exception $e) {
    // Log detalhado do erro
    error_log("ERRO CREATE_PREFERENCE: " . $e->getMessage());
    error_log("AMBIENTE: " . ($config['environment'] ?? 'desconhecido'));
    error_log("TOKENS CONFIGURADOS: " . ($config['has_valid_tokens'] ? 'SIM' : 'NÃO'));
    
    // Log específico de inscrição
    logInscricaoPagamento('ERROR', 'ERRO_GERAL_CREATE_PREFERENCE', [
        'inscricao_id' => $inscricao_id ?? null,
        'erro' => $e->getMessage(),
        'stack_trace' => $e->getTraceAsString(),
        'ambiente' => $config['environment'] ?? 'desconhecido'
    ]);
    
    if (function_exists('logMercadoPago')) {
        logMercadoPago('error', 'Erro ao criar preference', [
            'mensagem' => $e->getMessage()
        ]);
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
