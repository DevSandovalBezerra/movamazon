<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../db.php';
require_once '../helpers/inscricao_logger.php';
require_once '../mercadolivre/MercadoPagoClient.php';
require_once '../mercadolivre/payment_helper.php';

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

    $payment_method_id = $input['payment_method_id'] ?? '';
    $payment_type_id = $input['payment_type_id'] ?? '';

    if ($payment_method_id === '') {
        throw new Exception('Campo obrigatório não fornecido: payment_method_id');
    }

    // PIX/Boleto não usam token; devem usar endpoints dedicados
    $is_pix = $payment_method_id === 'pix';
    $is_boleto = $payment_type_id === 'ticket' || (is_string($payment_method_id) && str_starts_with($payment_method_id, 'bol'));
    if ($is_pix || $is_boleto) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Método de pagamento inválido para este endpoint.',
            'message' => 'Use o endpoint correto: create_pix.php ou create_boleto.php.',
            'redirect_endpoint' => $is_pix ? 'inscricao/create_pix.php' : 'inscricao/create_boleto.php'
        ]);
        exit;
    }

    // Validar campos obrigatórios (cartão)
    $required_fields = ['token', 'transaction_amount', 'payer'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field])) {
            throw new Exception("Campo obrigatório não fornecido: $field");
        }
    }

    // Validar dados do pagador
    if (!isset($input['payer']['email']) || !isset($input['payer']['identification'])) {
        throw new Exception('Dados do pagador incompletos');
    }

    $isPix = $payment_method_id === 'pix';

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
        'payment_method_id' => $payment_method_id,
        'token' => $input['token'],
        'transaction_amount' => (float)$input['transaction_amount']
    ];

    // Adicionar issuer_id apenas se não for PIX
    if (!$isPix && isset($input['issuer_id'])) {
        $payment_data['issuer_id'] = $input['issuer_id'];
    }

    // external_reference para webhook e rastreio
    $inscricao_id_input = isset($input['inscricao_id']) ? (int)$input['inscricao_id'] : 0;
    $inscricao_user_id = null;
    if ($inscricao_id_input > 0) {
        $payment_data['external_reference'] = 'MOVAMAZON_' . $inscricao_id_input;
    }

    // Items para melhorar aprovação (exigência MP): buscar por inscricao_id quando enviado
    if ($inscricao_id_input > 0) {
        $stmt_ins = $pdo->prepare("
            SELECT i.id, i.usuario_id, i.external_reference, i.valor_total, i.produtos_extras_ids,
                   e.nome as evento_nome, m.nome as modalidade_nome
            FROM inscricoes i
            LEFT JOIN eventos e ON i.evento_id = e.id
            LEFT JOIN modalidades m ON i.modalidade_evento_id = m.id
            WHERE i.id = ?
        ");
        $stmt_ins->execute([$inscricao_id_input]);
        $insc = $stmt_ins->fetch(PDO::FETCH_ASSOC);
        if ($insc) {
            $inscricao_user_id = $insc['usuario_id'] ?? null;
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

    // Registrar tentativa em pagamentos_ml (sem aguardar webhook)
    if ($inscricao_id_input > 0 && !empty($inscricao_user_id)) {
        try {
            $pdo->query("SELECT 1 FROM pagamentos_ml LIMIT 1");
            $payment_id_str = (string)$payment_result['id'];
            $status_mp = (string)($payment_result['status'] ?? '');
            $status_pagamento_ml = PaymentHelper::mapearStatusPagamentosML($status_mp);
            $preference_id = $payment_result['preference_id'] ?? ('card_' . $payment_id_str);
            $init_point = $payment_result['point_of_interaction']['transaction_data']['ticket_url']
                ?? $payment_result['transaction_details']['external_resource_url']
                ?? 'direct_payment';
            $dados_pagamento_json = json_encode($payment_result, JSON_UNESCAPED_UNICODE);
            $valor_pago = ($status_pagamento_ml === 'pago') ? ($payment_result['transaction_amount'] ?? null) : null;
            $metodo_pagamento = $payment_result['payment_method_id'] ?? $payment_method_id;
            $parcelas = $payment_result['installments'] ?? ($input['installments'] ?? 1);
            $taxa_ml = null;

            $stmt_check_ml = $pdo->prepare("SELECT id, status FROM pagamentos_ml WHERE payment_id = ? LIMIT 1");
            $stmt_check_ml->execute([$payment_id_str]);
            $pagamento_ml_existente = $stmt_check_ml->fetch(PDO::FETCH_ASSOC);

            if ($pagamento_ml_existente) {
                $status_final = $pagamento_ml_existente['status'] === 'pago' && $status_pagamento_ml !== 'pago'
                    ? 'pago'
                    : $status_pagamento_ml;
                $stmt_update_ml = $pdo->prepare("
                    UPDATE pagamentos_ml SET 
                        status = ?,
                        valor_pago = COALESCE(valor_pago, ?),
                        metodo_pagamento = COALESCE(metodo_pagamento, ?),
                        parcelas = COALESCE(parcelas, ?),
                        dados_pagamento = COALESCE(dados_pagamento, ?),
                        preference_id = COALESCE(preference_id, ?),
                        init_point = COALESCE(init_point, ?),
                        data_atualizacao = NOW()
                    WHERE id = ?
                ");
                $stmt_update_ml->execute([
                    $status_final,
                    $valor_pago,
                    $metodo_pagamento,
                    $parcelas,
                    $dados_pagamento_json,
                    $preference_id,
                    $init_point,
                    $pagamento_ml_existente['id']
                ]);
            } else {
                $stmt_insert_ml = $pdo->prepare("
                    INSERT INTO pagamentos_ml (
                        inscricao_id, preference_id, payment_id, init_point, status,
                        valor_pago, metodo_pagamento, parcelas, taxa_ml,
                        dados_pagamento, user_id, data_criacao, data_atualizacao
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $stmt_insert_ml->execute([
                    $inscricao_id_input,
                    $preference_id,
                    $payment_id_str,
                    $init_point,
                    $status_pagamento_ml,
                    $valor_pago,
                    $metodo_pagamento,
                    $parcelas,
                    $taxa_ml,
                    $dados_pagamento_json,
                    $inscricao_user_id
                ]);
            }
        } catch (Exception $e) {
            error_log("PROCESS_PAYMENT - Aviso: falha ao registrar pagamentos_ml: " . $e->getMessage());
            logInscricaoPagamento('WARNING', 'ERRO_REGISTRO_PAGAMENTOS_ML_CARTAO', [
                'inscricao_id' => $inscricao_id_input,
                'payment_id' => (string)$payment_result['id'],
                'erro' => $e->getMessage()
            ]);
        }
    }
    
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
