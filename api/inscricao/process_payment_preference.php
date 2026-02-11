<?php
header('Content-Type: application/json');
require_once '../db.php';
require_once '../helpers/inscricao_logger.php';
require_once '../mercadolivre/MercadoPagoClient.php';

// Configurações do Mercado Pago
$config = require '../mercadolivre/config.php';
$access_token = $config['accesstoken'];

// Validar token antes de usar
if (empty($access_token)) {
    logInscricaoPagamento('ERROR', 'TOKEN_NAO_CONFIGURADO_PROCESS_PAYMENT', [
        'ambiente' => $config['environment'] ?? 'desconhecido'
    ]);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Token de acesso do Mercado Pago não configurado'
    ]);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Dados não fornecidos');
    }

    // Validar campos obrigatórios
    $required_fields = ['token', 'payment_method_id', 'transaction_amount', 'payer'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field])) {
            throw new Exception("Campo obrigatório não fornecido: $field");
        }
    }

    // Validar dados do pagador
    if (!isset($input['payer']['email']) || !isset($input['payer']['identification'])) {
        throw new Exception('Dados do pagador incompletos');
    }

    $isPix = isset($input['payment_method_id']) && $input['payment_method_id'] === 'pix';

    $statement_descriptor = $config['statement_descriptor'] ?? 'MOVAMAZON';

    // Montar dados do pagamento
    $payment_data = [
        'description' => 'Pagamento de Inscrição - MovAmazonas',
        'statement_descriptor' => $statement_descriptor,
        'installments' => $input['installments'] ?? 1,
        'payer' => [
            'email' => $input['payer']['email'],
            'identification' => [
                'type' => $input['payer']['identification']['type'] ?? 'CPF',
                'number' => $input['payer']['identification']['number']
            ]
        ],
        'payment_method_id' => $input['payment_method_id'],
        'token' => $input['token'],
        'transaction_amount' => (float)$input['transaction_amount']
    ];

    // Adicionar issuer_id apenas se não for PIX
    if (!$isPix && isset($input['issuer_id'])) {
        $payment_data['issuer_id'] = $input['issuer_id'];
    }

    // external_reference para webhook e rastreio
    $inscricao_id_input = isset($input['inscricao_id']) ? (int)$input['inscricao_id'] : 0;
    if ($inscricao_id_input > 0) {
        $payment_data['external_reference'] = 'MOVAMAZON_' . $inscricao_id_input;
    }

    // Items para melhorar aprovação (exigência MP): buscar por inscricao_id quando enviado
    if ($inscricao_id_input > 0) {
        $stmt_ins = $pdo->prepare("
            SELECT i.id, i.valor_total, i.produtos_extras_ids,
                   e.nome as evento_nome, m.nome as modalidade_nome
            FROM inscricoes i
            LEFT JOIN eventos e ON i.evento_id = e.id
            LEFT JOIN modalidades m ON i.modalidade_evento_id = m.id
            WHERE i.id = ?
        ");
        $stmt_ins->execute([$inscricao_id_input]);
        $insc = $stmt_ins->fetch(PDO::FETCH_ASSOC);
        if ($insc) {
            $evento_nome = $insc['evento_nome'] ?? 'Evento';
            $modalidade_nome = $insc['modalidade_nome'] ?? 'Inscrição';
            $valor_total_ins = (float)$insc['valor_total'];
            $payment_data['items'] = [
                [
                    'id' => 'MOVAMAZON_' . $inscricao_id_input,
                    'title' => $modalidade_nome,
                    'description' => 'Inscrição no evento: ' . $evento_nome,
                    'category_id' => 'EBL-Evento Desportivo',
                    'quantity' => 1,
                    'unit_price' => $valor_total_ins,
                    'currency_id' => 'BRL'
                ]
            ];
            $produtos_extras = [];
            if (!empty($insc['produtos_extras_ids'])) {
                $decoded = json_decode($insc['produtos_extras_ids'], true);
                if (is_array($decoded)) {
                    $produtos_extras = $decoded;
                }
            }
            $idx = 0;
            foreach ($produtos_extras as $p) {
                if (isset($p['nome']) && isset($p['valor']) && (float)$p['valor'] > 0) {
                    $payment_data['items'][] = [
                        'id' => 'extra_' . $inscricao_id_input . '_' . $idx,
                        'title' => $p['nome'],
                        'description' => $p['descricao'] ?? 'Produto adicional',
                        'category_id' => 'EBL-Evento Desportivo',
                        'quantity' => 1,
                        'unit_price' => (float)$p['valor'],
                        'currency_id' => 'BRL'
                    ];
                    $idx++;
                }
            }
        }
    }

    // Processar pagamento via MercadoPagoClient (SDK ou cURL)
    $client = new MercadoPagoClient($config);
    $payment_result = $client->createPayment($payment_data);

    if (!isset($payment_result['id'])) {
        throw new Exception('Erro ao processar pagamento: resposta inválida do Mercado Pago');
    }

    // Log da operação
    error_log("Pagamento processado: " . $payment_result['id'] . " - Status: " . $payment_result['status']);
    
    // Log success: pagamento processado
    logInscricaoPagamento('SUCCESS', 'PAGAMENTO_PROCESSADO', [
        'payment_id' => (string)$payment_result['id'],
        'status' => $payment_result['status'],
        'status_detail' => $payment_result['status_detail'] ?? null,
        'transaction_amount' => $payment_result['transaction_amount'] ?? null,
        'payment_method_id' => $payment_result['payment_method_id'] ?? null
    ]);

    // Retornar resposta
    echo json_encode([
        'success' => true,
        'id' => $payment_result['id'],
        'status' => $payment_result['status'],
        'status_detail' => $payment_result['status_detail'],
        'transaction_amount' => $payment_result['transaction_amount'],
        'payment_method_id' => $payment_result['payment_method_id']
    ]);
} catch (Exception $e) {
    error_log("Erro ao processar pagamento: " . $e->getMessage());
    
    // Log error
    logInscricaoPagamento('ERROR', 'ERRO_PROCESSAMENTO_PAGAMENTO', [
        'erro' => $e->getMessage(),
        'stack_trace' => $e->getTraceAsString()
    ]);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
