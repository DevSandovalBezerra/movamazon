<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../db.php';
require_once '../config/email_config.php';
require_once '../helpers/inscricao_logger.php';
require_once '../helpers/cpf_validator.php';
require_once '../helpers/cancelar_inscricoes_expiradas_helper.php';
require_once __DIR__ . '/../mercadolivre/MercadoPagoClient.php';

try {
    // Verificar se é POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    // Ler dados do POST
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception('Dados inválidos');
    }

    // Validar dados obrigatórios
    $required_fields = ['inscricao_id', 'valor_total'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Campo obrigatório: $field");
        }
    }

    $inscricaoId = (int) $data['inscricao_id'];
    $valorTotal = (float) $data['valor_total'];
    
    // Validar valores
    if ($inscricaoId <= 0) {
        throw new Exception('ID da inscrição inválido');
    }
    
    if ($valorTotal <= 0) {
        throw new Exception('Valor total inválido: ' . $valorTotal);
    }
    
    error_log("PIX INSCRICAO - Validando: inscricao_id=$inscricaoId, valor_total=$valorTotal");
    
    // Log início da geração de PIX
    logInscricaoPagamento('INFO', 'INICIO_GERACAO_PIX', [
        'inscricao_id' => $inscricaoId,
        'valor_total' => $valorTotal
    ]);

    // ✅ FALLBACK 2: Verificar e cancelar inscrições expiradas antes de gerar PIX
    // Executa silenciosamente para não impactar performance
    cancelarInscricoesExpiradas($pdo, true);
    
    // Buscar dados da inscrição com dados do usuário e modalidade (para items)
    $stmt = $pdo->prepare("
        SELECT i.*, e.nome as evento_nome, e.descricao as evento_descricao,
               m.nome as modalidade_nome,
               u.email as usuario_email, u.documento as usuario_cpf
        FROM inscricoes i
        JOIN eventos e ON i.evento_id = e.id
        LEFT JOIN modalidades m ON i.modalidade_evento_id = m.id
        JOIN usuarios u ON i.usuario_id = u.id
        WHERE i.id = ?
    ");
    $stmt->execute([$inscricaoId]);
    $inscricao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inscricao) {
        throw new Exception('Inscrição não encontrada');
    }
    
    // Verificar se a inscrição foi cancelada após verificação automática
    if ($inscricao['status'] === 'cancelada' || $inscricao['status_pagamento'] === 'cancelado') {
        throw new Exception('Esta inscrição foi cancelada automaticamente por expiração. Por favor, faça uma nova inscrição.');
    }

    // Configuração do Mercado Pago
    $config = require_once __DIR__ . '/../mercadolivre/config.php';
    
    // Validar se tokens estão configurados
    if (!$config['has_valid_tokens']) {
        throw new Exception('Tokens do Mercado Pago não configurados. Verifique o arquivo .env');
    }
    
    $accesstoken = $config['accesstoken'];
    
    // Validar email e CPF do usuário (dados reais obrigatórios)
    $email = $inscricao['usuario_email'] ?? '';
    $cpf = $inscricao['usuario_cpf'] ?? '';
    
    // Remover caracteres não numéricos do CPF
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    // Validação rigorosa de email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("PIX INSCRICAO ERROR - Email inválido ou ausente: " . ($email ?: 'vazio'));
        logInscricaoPagamento('ERROR', 'VALIDACAO_EMAIL_FALHOU', [
            'inscricao_id' => $inscricaoId,
            'email' => $email ?: 'vazio'
        ]);
        throw new Exception('Email do pagador não encontrado ou inválido. Verifique os dados da inscrição.');
    }
    
    // Validação de comprimento do CPF
    if (empty($cpf) || strlen($cpf) !== 11) {
        error_log("PIX INSCRICAO ERROR - CPF com comprimento inválido: " . strlen($cpf) . " dígitos");
        logInscricaoPagamento('WARNING', 'VALIDACAO_CPF_FALHOU', [
            'inscricao_id' => $inscricaoId,
            'usuario_id' => $inscricao['usuario_id'] ?? null,
            'cpf_length' => strlen($cpf),
            'campo' => 'cpf',
            'erro' => 'comprimento_invalido'
        ]);
        
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'CPF do pagador não encontrado ou inválido. Verifique os dados cadastrais.',
            'message' => 'CPF do pagador não encontrado ou inválido. Verifique os dados cadastrais.',
            'campos_faltantes' => ['cpf']
        ]);
        exit;
    }
    
    // Validar CPF com algoritmo brasileiro (dígitos verificadores)
    if (!validarCPF($cpf)) {
        error_log("PIX INSCRICAO ERROR - CPF com dígitos verificadores inválidos: " . substr($cpf, 0, 3) . '***');
        logInscricaoPagamento('WARNING', 'CPF_INVALIDO', [
            'inscricao_id' => $inscricaoId,
            'usuario_id' => $inscricao['usuario_id'] ?? null,
            'campo' => 'cpf',
            'erro' => 'digitos_verificadores_incorretos',
            'valor' => substr($cpf, 0, 3) . '***' . substr($cpf, -2)
        ]);
        
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'CPF inválido. O CPF informado não é válido segundo a Receita Federal. Verifique seus dados cadastrais e corrija o CPF.',
            'message' => 'CPF inválido. O CPF informado não é válido segundo a Receita Federal. Verifique seus dados cadastrais e corrija o CPF.',
            'detalhes' => 'O CPF possui dígitos verificadores incorretos.',
            'campos_faltantes' => ['cpf']
        ]);
        exit;
    }

    // Usar external_reference da inscrição para consistência com webhook
    $external_ref = $inscricao['external_reference'] ?: 'MOVAMAZON_' . $inscricaoId;
    
    // Montar items para exigência MP (melhor taxa de aprovação)
    $evento_nome = $inscricao['evento_nome'] ?? 'Evento';
    $modalidade_nome = $inscricao['modalidade_nome'] ?? 'Inscrição';
    $pix_items = [
        [
            'id' => 'MOVAMAZON_' . $inscricaoId,
            'title' => $modalidade_nome,
            'description' => 'Inscrição no evento: ' . $evento_nome,
            'category_id' => 'EBL-Evento Desportivo',
            'quantity' => 1,
            'unit_price' => (float) $valorTotal,
            'currency_id' => 'BRL'
        ]
    ];
    $produtos_extras = [];
    if (!empty($inscricao['produtos_extras_ids'])) {
        $decoded = json_decode($inscricao['produtos_extras_ids'], true);
        if (is_array($decoded)) {
            $produtos_extras = $decoded;
        }
    }
    $idx = 0;
    foreach ($produtos_extras as $p) {
        if (isset($p['nome']) && isset($p['valor']) && (float)$p['valor'] > 0) {
            $pix_items[] = [
                'id' => 'extra_' . $inscricaoId . '_' . $idx,
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

    // Preparar payload PIX com dados reais validados
    // NOTA: Campo 'items' foi removido pois a API /v1/payments para payments diretos (PIX/Boleto) NÃO aceita 'items'
    // O campo 'items' é válido apenas para Preferences e payments vinculados a preferences
    // Código de montagem de $pix_items mantido comentado para referência futura
    $statement_descriptor = $config['statement_descriptor'] ?? 'MOVAMAZON';
    $pix_payload = [
        "description" => "Inscrição no evento: " . $evento_nome,
        "external_reference" => $external_ref,
        "notification_url" => $config['url_notification_api'],
        "statement_descriptor" => $statement_descriptor,
        "payer" => [
            "email" => $email,
            "identification" => [
                "type" => "CPF",
                "number" => $cpf
            ]
        ],
        "payment_method_id" => "pix",
        "transaction_amount" => (float) $valorTotal
        // "items" => $pix_items // REMOVIDO: API /v1/payments não aceita items para payments diretos
    ];
    
    // Validar que transaction_amount não é null ou zero
    if (empty($pix_payload['transaction_amount']) || $pix_payload['transaction_amount'] <= 0) {
        throw new Exception("Valor da transação inválido: " . $valorTotal);
    }
    
    // Log detalhado do payload enviado
    error_log("PIX INSCRICAO - Payload enviado: " . json_encode($pix_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    // Criar pagamento PIX via MercadoPagoClient (SDK ou cURL)
    $client = new MercadoPagoClient($config);
    $pixData = $client->createPayment($pix_payload);

    error_log("PIX INSCRICAO - API Response OK, payment_id: " . ($pixData['id'] ?? 'N/A'));

    if (!isset($pixData['id']) || !isset($pixData['point_of_interaction']['transaction_data'])) {
        logInscricaoPagamento('ERROR', 'RESPOSTA_INVALIDA_MERCADO_PAGO', [
            'inscricao_id' => $inscricaoId,
            'erro' => 'Resposta inválida do Mercado Pago',
            'resposta' => json_encode($pixData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        ]);
        throw new Exception('Resposta inválida do Mercado Pago');
    }

    // Extrair dados do PIX
    $paymentId = $pixData['id'];
    $qrCode = $pixData['point_of_interaction']['transaction_data']['qr_code'];
    $qrCodeBase64 = $pixData['point_of_interaction']['transaction_data']['qr_code_base64'];
    $ticketUrl = $pixData['point_of_interaction']['transaction_data']['ticket_url'];

    // Atualizar inscrição com status de pagamento pendente
    // O payment_id será armazenado na tabela pagamentos_ml pelo webhook quando o pagamento for confirmado
    $status_pagamento = 'processando';
    if (isset($pixData['status'])) {
        // Mapear status do Mercado Pago para status da inscrição
        $status_map = [
            'approved' => 'pago',
            'pending' => 'processando',
            'rejected' => 'rejeitado',
            'cancelled' => 'cancelado'
        ];
        $status_pagamento = $status_map[$pixData['status']] ?? 'processando';
    }
    
    $stmt = $pdo->prepare("
        UPDATE inscricoes 
        SET status_pagamento = ?,
            forma_pagamento = 'pix',
            external_reference = ?
        WHERE id = ?
    ");
    $stmt->execute([$status_pagamento, (string)$paymentId, $inscricaoId]);
    
    error_log("PIX INSCRICAO - Inscrição atualizada: ID=$inscricaoId, Status=$status_pagamento, PaymentID=$paymentId");
    
    // Log success: PIX gerado com sucesso
    logInscricaoPagamento('SUCCESS', 'PIX_GERADO', [
        'inscricao_id' => $inscricaoId,
        'payment_id' => (string)$paymentId,
        'valor_total' => $valorTotal,
        'status_pagamento' => $status_pagamento,
        'forma_pagamento' => 'pix',
        'qr_code' => substr($qrCode, 0, 20) . '***' // Parcial para log
    ]);

    // Retornar dados do PIX
    echo json_encode([
        'success' => true,
        'payment_id' => $paymentId,
        'qr_code' => $qrCode,
        'qr_code_base64' => $qrCodeBase64,
        'ticket_url' => $ticketUrl,
        'transaction_amount' => $valorTotal,
        'external_reference' => $inscricaoId,
        'evento_nome' => $inscricao['evento_nome']
    ]);
} catch (Exception $e) {
    $error_message = $e->getMessage();
    error_log("PIX INSCRICAO ERROR: " . $error_message);
    error_log("PIX INSCRICAO ERROR - Stack trace: " . $e->getTraceAsString());
    
    // Log error final
    logInscricaoPagamento('ERROR', 'ERRO_GERACAO_PIX', [
        'inscricao_id' => $inscricaoId ?? null,
        'erro' => $error_message,
        'stack_trace' => $e->getTraceAsString()
    ]);
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $error_message,
        'error_code' => 'PIX_GENERATION_FAILED'
    ]);
}
