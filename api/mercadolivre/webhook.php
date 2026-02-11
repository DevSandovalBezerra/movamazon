<?php
/**
 * ⚡ WEBHOOK OTIMIZADO - RESPONDE HTTP 200 EM < 500ms
 * 
 * ESTRATÉGIA:
 * 1. Recebe notificação do Mercado Pago
 * 2. Valida dados básicos
 * 3. Salva em fila para processamento assíncrono
 * 4. Responde HTTP 200 IMEDIATAMENTE (< 100ms)
 * 5. Processa dados em background
 */

$start_time = microtime(true);

// ✅ DEBUG CRÍTICO: Log no php_errors.log SEMPRE
error_log("========== WEBHOOK MP INICIADO (OTIMIZADO) ==========");
error_log("[WEBHOOK] Timestamp: " . date('Y-m-d H:i:s'));
error_log("[WEBHOOK] IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

// Usar caminhos absolutos
$base_path = dirname(dirname(__DIR__));
$log_file = $base_path . '/logs/webhook_mp.log';
$queue_file = $base_path . '/logs/webhook_queue.json';

// ✅ RESPOSTA RÁPIDA: Capturar dados e responder IMEDIATAMENTE
header('Content-Type: application/json');
$body = file_get_contents('php://input');
$data = json_decode($body, true);

error_log("[WEBHOOK] Body: " . substr($body, 0, 200));
error_log("[WEBHOOK] Type: " . ($data['type'] ?? 'NULL'));

// Garantir pasta de logs
$log_dir = dirname($log_file);
if (!is_dir($log_dir)) {
    @mkdir($log_dir, 0755, true);
}

// ✅ VALIDAÇÃO RÁPIDA: Apenas verificar se é notificação válida
if (!isset($data['type']) || $data['type'] !== 'payment') {
    error_log("[WEBHOOK] ⚠️ Tipo não é payment, ignorando");
    $retorno = ['status' => 'ignored', 'reason' => 'not_payment_type'];
    @file_put_contents(__DIR__ . '/webhook_retorno.txt', date('Y-m-d H:i:s') . ' ' . json_encode($retorno, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
    http_response_code(200);
    echo json_encode($retorno);
    exit();
}

$payment_id = $data['data']['id'] ?? null;
if (!$payment_id) {
    error_log("[WEBHOOK] ❌ Payment ID ausente");
    $retorno = ['status' => 'error', 'reason' => 'missing_payment_id'];
    @file_put_contents(__DIR__ . '/webhook_retorno.txt', date('Y-m-d H:i:s') . ' ' . json_encode($retorno, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
    http_response_code(200); // ✅ IMPORTANTE: Retornar 200 mesmo com erro para não retentar
    echo json_encode($retorno);
    exit();
}

// ✅ VALIDAÇÃO DA ASSINATURA (x-signature) - Documentação: https://www.mercadopago.com.mx/developers/es/docs/checkout-pro/additional-content/notifications/webhooks
$config = @include __DIR__ . '/config.php';
$webhook_secret = is_array($config) && !empty($config['secret_key_webhook']) ? $config['secret_key_webhook'] : '';
if ($webhook_secret !== '') {
    $x_signature = $_SERVER['HTTP_X_SIGNATURE'] ?? '';
    $x_request_id = $_SERVER['HTTP_X_REQUEST_ID'] ?? '';
    $data_id = isset($_GET['data.id']) ? $_GET['data.id'] : (string)$payment_id;
    if (is_string($data_id) && preg_match('/^[a-zA-Z0-9]+$/', $data_id)) {
        $data_id = strtolower($data_id);
    }
    $ts = null;
    $hash = null;
    if ($x_signature !== '') {
        $parts = explode(',', $x_signature);
        foreach ($parts as $part) {
            $key_value = explode('=', $part, 2);
            if (count($key_value) === 2) {
                $key = trim($key_value[0]);
                $value = trim($key_value[1]);
                if ($key === 'ts') {
                    $ts = $value;
                } elseif ($key === 'v1') {
                    $hash = $value;
                }
            }
        }
    }
    $signature_valid = false;
    if ($ts !== null && $hash !== null) {
        $parts_manifest = [];
        $parts_manifest[] = 'id:' . $data_id;
        if ($x_request_id !== '') {
            $parts_manifest[] = 'request-id:' . $x_request_id;
        }
        $parts_manifest[] = 'ts:' . $ts;
        $manifest = implode(';', $parts_manifest) . ';';
        $expected_hash = hash_hmac('sha256', $manifest, $webhook_secret);
        $signature_valid = hash_equals($expected_hash, $hash);
    }
    if (!$signature_valid) {
        error_log("[WEBHOOK] ❌ Assinatura x-signature inválida ou ausente (payment_id: $payment_id)");
        $retorno = ['status' => 'error', 'reason' => 'signature_invalid'];
        @file_put_contents(__DIR__ . '/webhook_retorno.txt', date('Y-m-d H:i:s') . ' ' . json_encode($retorno, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
        @file_put_contents($log_file, date('Y-m-d H:i:s') . " [REJEITADO] Assinatura inválida payment_id: $payment_id\n", FILE_APPEND);
        http_response_code(200);
        echo json_encode($retorno);
        exit();
    }
    error_log("[WEBHOOK] ✅ Assinatura x-signature validada (payment_id: $payment_id)");
}

// ✅ ADICIONAR À FILA: Salvar para processar depois
$queue_item = [
    'payment_id' => $payment_id,
    'data' => $data,
    'received_at' => date('Y-m-d H:i:s'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
];

// Carregar fila existente
$queue = [];
if (file_exists($queue_file)) {
    $queue_content = @file_get_contents($queue_file);
    if ($queue_content) {
        $queue = json_decode($queue_content, true) ?: [];
    }
}

// Adicionar à fila
$queue[] = $queue_item;
@file_put_contents($queue_file, json_encode($queue, JSON_PRETTY_PRINT));

// ✅ LOG RÁPIDO
@file_put_contents($log_file, date('Y-m-d H:i:s') . " [RECEBIDO] Payment: $payment_id (fila: " . count($queue) . ")\n", FILE_APPEND);

$elapsed = round((microtime(true) - $start_time) * 1000, 2);
error_log("[WEBHOOK] ⚡ Resposta em {$elapsed}ms (payment_id: $payment_id)");

$retorno = [
    'status' => 'ok',
    'payment_id' => $payment_id,
    'queued' => true,
    'response_time_ms' => $elapsed
];
$retorno_json = json_encode($retorno, JSON_UNESCAPED_UNICODE);

// ✅ Gravar retorno em arquivo de texto na pasta do webhook
@file_put_contents(__DIR__ . '/webhook_retorno.txt', date('Y-m-d H:i:s') . ' ' . $retorno_json . "\n", FILE_APPEND);

// ✅ RESPONDER HTTP 200 IMEDIATAMENTE
http_response_code(200);
echo $retorno_json;

// ✅ FECHAR CONEXÃO E PROCESSAR EM BACKGROUND
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request(); // Fecha conexão HTTP
}

// =========================================
// ⚡ PROCESSAMENTO ASSÍNCRONO (APÓS HTTP 200)
// =========================================

error_log("[WEBHOOK] 🔄 Iniciando processamento assíncrono...");

require_once $base_path . '/api/db.php';
require_once $base_path . '/api/helpers/email_helper.php';
require_once $base_path . '/api/helpers/inscricao_logger.php';

// ✅ CONSULTAR API DO MERCADO PAGO
$config = require __DIR__ . '/config.php';

// Validação: verificar se token está configurado
if (empty($config['accesstoken'] ?? '')) {
    error_log("[WEBHOOK] ❌ Access token não configurado");
    exit(); // Já respondeu HTTP 200, só registra erro
}

require_once __DIR__ . '/MercadoPagoClient.php';

// Log de auditoria
if (function_exists('logMercadoPago')) {
    logMercadoPago('webhook', 'Processamento assíncrono', [
        'environment' => $config['environment'] ?? 'desconhecido',
        'payment_id' => $payment_id
    ]);
}

try {
    $client = new MercadoPagoClient($config);
    $payment_data = $client->getPayment((string) $payment_id);
} catch (Exception $e) {
    error_log("[WEBHOOK] ❌ Erro ao consultar MP: " . $e->getMessage());
    @file_put_contents($log_file, date('Y-m-d H:i:s') . " [ERRO] ao consultar payment $payment_id: " . $e->getMessage() . "\n", FILE_APPEND);
    exit(); // Já respondeu HTTP 200
}
$external_reference = $payment_data['external_reference'] ?? null;
$status = $payment_data['status'] ?? null;

@file_put_contents($log_file, date('Y-m-d H:i:s') . " [PROCESSANDO] Payment: $payment_id, Status: $status, Ref: $external_reference\n", FILE_APPEND);

// Log webhook recebido
logInscricaoPagamento('INFO', 'WEBHOOK_PROCESSAMENTO', [
    'payment_id' => (string)$payment_id,
    'status' => $status,
    'external_reference' => $external_reference
]);

if (!$external_reference || !$status) {
    logInscricaoPagamento('WARNING', 'WEBHOOK_DADOS_INCOMPLETOS', [
        'payment_id' => (string)$payment_id,
        'external_reference' => $external_reference,
        'status' => $status
    ]);
    error_log("[WEBHOOK] ⚠️ Dados incompletos: ref=$external_reference, status=$status");
    exit(); // Já respondeu HTTP 200
}

try {
    // Verificar se a tabela pagamentos_ml existe
    $hasPagamentosMl = false;
    try {
        $stmtTbl = $pdo->query("SHOW TABLES LIKE 'pagamentos_ml'");
        $hasPagamentosMl = $stmtTbl && $stmtTbl->rowCount() > 0;
    } catch (Exception $e) {
        $hasPagamentosMl = false;
        error_log("[WEBHOOK] Aviso: tabela pagamentos_ml: " . $e->getMessage());
    }

    $pdo->beginTransaction();

    // ✅ BUSCAR INSCRIÇÃO (1º por ref do MP; 2º por payment_id — PIX grava payment_id em external_reference)
    $stmt_find = $pdo->prepare(
        "SELECT i.id, i.usuario_id, i.valor_total, i.tamanho_camiseta, i.status_pagamento, u.email as user_email, u.nome_completo as user_name, e.nome as event_name
         FROM inscricoes i
         JOIN usuarios u ON i.usuario_id = u.id
         JOIN eventos e ON i.evento_id = e.id
         WHERE i.external_reference = ?"
    );
    $stmt_find->execute([$external_reference]);
    $inscricao = $stmt_find->fetch(PDO::FETCH_ASSOC);

    if (!$inscricao) {
        $stmt_find->execute([(string)$payment_id]);
        $inscricao = $stmt_find->fetch(PDO::FETCH_ASSOC);
    }

    if (!$inscricao) {
        logInscricaoPagamento('WARNING', 'INSCRICAO_NAO_ENCONTRADA_WEBHOOK', [
            'payment_id' => (string)$payment_id,
            'external_reference' => $external_reference,
            'status' => $status
        ]);
        error_log("[WEBHOOK] ⚠️ Inscrição não encontrada: ref=$external_reference nem payment_id=$payment_id");
        $pdo->rollBack();
        exit(); // Já respondeu HTTP 200
    }

    $inscricao_id = $inscricao['id'];
    $user_id = $inscricao['usuario_id'];
    $valor_total_inscricao = (float)($inscricao['valor_total'] ?? 0);
    $tamanho_camiseta = $inscricao['tamanho_camiseta'];

    // Mapear status do ML
    $status_inscricao_map = [
        'approved' => 'pago',
        'pending' => 'pendente',
        'in_process' => 'processando',
        'rejected' => 'rejeitado',
        'cancelled' => 'cancelado',
        'refunded' => 'reembolsado'
    ];

    $status_pagamentos_ml_map = [
        'approved' => 'pago',
        'pending' => 'pendente',
        'in_process' => 'processando',
        'rejected' => 'rejeitado',
        'cancelled' => 'cancelado',
        'refunded' => 'cancelado'
    ];

    $status_pagamentos_map = [
        'approved' => 'pago',
        'pending' => 'pendente',
        'in_process' => 'pendente',
        'rejected' => 'pendente',
        'cancelled' => 'cancelado',
        'refunded' => 'cancelado'
    ];

    $novo_status_inscricao = $status_inscricao_map[$status] ?? 'pendente';
    $novo_status_pagamentos_ml = $status_pagamentos_ml_map[$status] ?? 'pendente';
    $novo_status_pagamentos = $status_pagamentos_map[$status] ?? 'pendente';

    // Extrair dados do pagamento
    $valor_pago = (float)($payment_data['transaction_amount'] ?? 0);
    $metodo_pagamento = $payment_data['payment_method_id'] ?? $payment_data['payment_type_id'] ?? null;
    $parcelas = (int)($payment_data['installments'] ?? 1);
    $taxa_ml = 0.0;
    if (!empty($payment_data['fee_details']) && is_array($payment_data['fee_details'])) {
        foreach ($payment_data['fee_details'] as $fee) {
            if (isset($fee['amount'])) {
                $taxa_ml += (float)$fee['amount'];
            }
        }
    }

    // ✅ ATUALIZAR INSCRIÇÃO (status sempre valor válido do enum)
    $novo_status = ($novo_status_inscricao === 'pago')
        ? 'confirmada'
        : (isset($inscricao['status']) && in_array($inscricao['status'], ['pendente', 'confirmada', 'cancelada'], true)
            ? $inscricao['status']
            : 'pendente');

    $stmt_update = $pdo->prepare(
        "UPDATE inscricoes SET 
            status = ?,
            status_pagamento = ?, 
            data_pagamento = ?, 
            forma_pagamento = 'mercadolivre'
            WHERE id = ?"
    );

    $data_pagamento = ($novo_status_inscricao === 'pago') ? date('Y-m-d H:i:s') : null;
    $status_anterior = $inscricao['status_pagamento'];
    $stmt_update->execute([$novo_status, $novo_status_inscricao, $data_pagamento, $inscricao_id]);
    
    error_log("[WEBHOOK] ✅ Inscrição $inscricao_id: $status_anterior → $novo_status_inscricao");
    
    logInscricaoPagamento('SUCCESS', 'STATUS_ATUALIZADO', [
        'inscricao_id' => $inscricao_id,
        'payment_id' => (string)$payment_id,
        'status_anterior' => $status_anterior,
        'status_novo' => $novo_status_inscricao,
        'valor_pago' => $valor_pago
    ]);

    // ✅ ATUALIZAR TABELA pagamentos_ml (se existir)
    if ($hasPagamentosMl) {
        $stmt_check = $pdo->prepare("SELECT id, preference_id, init_point FROM pagamentos_ml WHERE inscricao_id = ? LIMIT 1");
        $stmt_check->execute([$inscricao_id]);
        $pagamento_existente = $stmt_check->fetch(PDO::FETCH_ASSOC);

        $preference_id = $pagamento_existente['preference_id'] ?? '';
        $init_point = $pagamento_existente['init_point'] ?? '';

        $stmt_check_pagamentos_ml = $pdo->prepare("SELECT id FROM pagamentos_ml WHERE payment_id = ? LIMIT 1");
        $stmt_check_pagamentos_ml->execute([$payment_id]);
        $pagamento_ml_existente = $stmt_check_pagamentos_ml->fetch(PDO::FETCH_ASSOC);

        $dados_pagamento_json = json_encode($payment_data, JSON_UNESCAPED_UNICODE);

        if ($pagamento_ml_existente) {
            $stmt_pagamentos_ml = $pdo->prepare(
                "UPDATE pagamentos_ml SET 
                    payment_id = ?,
                    status = ?, 
                    valor_pago = ?,
                    metodo_pagamento = ?,
                    parcelas = ?,
                    taxa_ml = ?,
                    dados_pagamento = ?,
                    data_atualizacao = NOW()
                    WHERE id = ?"
            );
            $stmt_pagamentos_ml->execute([
                $payment_id,
                $novo_status_pagamentos_ml,
                $valor_pago,
                $metodo_pagamento,
                $parcelas,
                $taxa_ml,
                $dados_pagamento_json,
                $pagamento_ml_existente['id']
            ]);
        } else {
            $preference_id_final = !empty($preference_id) ? $preference_id : 'webhook_' . $payment_id;
            $init_point_final = !empty($init_point) ? $init_point : 'https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=' . $preference_id_final;
            
            $stmt_pagamentos_ml = $pdo->prepare(
                "INSERT INTO pagamentos_ml (
                    inscricao_id, payment_id, preference_id, init_point, status,
                    valor_pago, metodo_pagamento, parcelas, taxa_ml,
                    dados_pagamento, user_id, data_criacao, data_atualizacao
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
            );
            $stmt_pagamentos_ml->execute([
                $inscricao_id,
                $payment_id,
                $preference_id_final,
                $init_point_final,
                $novo_status_pagamentos_ml,
                $valor_pago,
                $metodo_pagamento,
                $parcelas,
                $taxa_ml,
                $dados_pagamento_json,
                $user_id
            ]);
        }
    }

    // ✅ ATUALIZAR TABELA pagamentos (idempotente por payment_id quando coluna existir)
    error_log("[WEBHOOK] 💾 Salvando na tabela pagamentos...");
    $has_payment_id_col = false;
    try {
        $pdo->query("SELECT payment_id FROM pagamentos LIMIT 0");
        $has_payment_id_col = true;
    } catch (Exception $e) {
        $has_payment_id_col = false;
    }

    if ($has_payment_id_col) {
        $stmt_by_payment = $pdo->prepare("SELECT id, inscricao_id, status FROM pagamentos WHERE payment_id = ? LIMIT 1");
        $stmt_by_payment->execute([$payment_id]);
        $row_by_payment = $stmt_by_payment->fetch(PDO::FETCH_ASSOC);
        if ($row_by_payment) {
            error_log("[WEBHOOK] ♻️ Atualizando pagamento por payment_id ID: " . $row_by_payment['id']);
            $stmt_pagamentos = $pdo->prepare(
                "UPDATE pagamentos SET status = ?, valor_pago = ?, forma_pagamento = ?, data_pagamento = NOW() WHERE id = ?"
            );
            $stmt_pagamentos->execute([$novo_status_pagamentos, $valor_pago, $metodo_pagamento, $row_by_payment['id']]);
            error_log("[WEBHOOK] ✅ Pagamento atualizado: status=$novo_status_pagamentos, valor=$valor_pago");
        } else {
            $stmt_by_insc = $pdo->prepare("SELECT id FROM pagamentos WHERE inscricao_id = ? AND status = 'pago' LIMIT 1");
            $stmt_by_insc->execute([$inscricao_id]);
            $row_by_insc = $stmt_by_insc->fetch(PDO::FETCH_ASSOC);
            if ($row_by_insc) {
                error_log("[WEBHOOK] ♻️ Atualizando pagamento existente e setando payment_id ID: " . $row_by_insc['id']);
                $stmt_pagamentos = $pdo->prepare(
                    "UPDATE pagamentos SET payment_id = ?, status = ?, valor_pago = ?, forma_pagamento = ?, data_pagamento = NOW() WHERE id = ?"
                );
                $stmt_pagamentos->execute([$payment_id, $novo_status_pagamentos, $valor_pago, $metodo_pagamento, $row_by_insc['id']]);
                error_log("[WEBHOOK] ✅ Pagamento atualizado com payment_id");
            } else {
                error_log("[WEBHOOK] 🆕 Inserindo NOVO pagamento com payment_id");
                $stmt_pagamentos = $pdo->prepare(
                    "INSERT INTO pagamentos (inscricao_id, forma_pagamento, data_pagamento, valor_total, valor_pago, status, payment_id) VALUES (?, ?, NOW(), ?, ?, ?, ?)"
                );
                $stmt_pagamentos->execute([
                    $inscricao_id,
                    $metodo_pagamento,
                    $valor_total_inscricao ?: $valor_pago,
                    $valor_pago,
                    $novo_status_pagamentos,
                    $payment_id
                ]);
                $last_insert_id = $pdo->lastInsertId();
                error_log("[WEBHOOK] ✅ Pagamento inserido! ID: $last_insert_id");
            }
        }
    } else {
        $stmt_check_pagamentos = $pdo->prepare("SELECT id FROM pagamentos WHERE inscricao_id = ? AND status = 'pago' LIMIT 1");
        $stmt_check_pagamentos->execute([$inscricao_id]);
        $pagamento_geral_existente = $stmt_check_pagamentos->fetch(PDO::FETCH_ASSOC);
        if ($pagamento_geral_existente) {
            error_log("[WEBHOOK] ♻️ Atualizando pagamento ID: " . $pagamento_geral_existente['id']);
            $stmt_pagamentos = $pdo->prepare(
                "UPDATE pagamentos SET status = ?, valor_pago = ?, forma_pagamento = ?, data_pagamento = NOW() WHERE inscricao_id = ?"
            );
            $stmt_pagamentos->execute([$novo_status_pagamentos, $valor_pago, $metodo_pagamento, $inscricao_id]);
            error_log("[WEBHOOK] ✅ Pagamento atualizado: status=$novo_status_pagamentos, valor=$valor_pago");
        } else {
            error_log("[WEBHOOK] 🆕 Inserindo NOVO pagamento");
            $stmt_pagamentos = $pdo->prepare(
                "INSERT INTO pagamentos (inscricao_id, forma_pagamento, data_pagamento, valor_total, valor_pago, status) VALUES (?, ?, NOW(), ?, ?, ?)"
            );
            $stmt_pagamentos->execute([
                $inscricao_id,
                $metodo_pagamento,
                $valor_total_inscricao ?: $valor_pago,
                $valor_pago,
                $novo_status_pagamentos
            ]);
            $last_insert_id = $pdo->lastInsertId();
            error_log("[WEBHOOK] ✅ Pagamento inserido! ID: $last_insert_id");
        }
    }

    // ✅ ATUALIZAR ESTOQUE DE CAMISAS (se pago)
    if ($novo_status_inscricao === 'pago' && $tamanho_camiseta) {
        $stmt_estoque = $pdo->prepare(
            "UPDATE camisas SET quantidade_vendida = quantidade_vendida + 1, quantidade_disponivel = quantidade_disponivel - 1 WHERE tamanho = ? AND evento_id = (SELECT evento_id FROM inscricoes WHERE id = ?)"
        );
        $stmt_estoque->execute([$tamanho_camiseta, $inscricao_id]);
        error_log("[WEBHOOK] 👕 Estoque atualizado: tamanho $tamanho_camiseta");
    }

    $pdo->commit();
    error_log("[WEBHOOK] ✅ COMMIT SUCESSO! Payment $payment_id → status: $novo_status_inscricao");

    @file_put_contents($log_file, date('Y-m-d H:i:s') . " [SUCESSO] Payment $payment_id → $novo_status_inscricao (Ref: $external_reference)\n", FILE_APPEND);

    // ✅ ENVIAR EMAIL (se pago); falha de email não propaga
    if ($novo_status_inscricao === 'pago') {
        try {
            error_log("[WEBHOOK] 📧 Enviando email para: " . $inscricao['user_email']);
            require_once __DIR__ . '/../helpers/email_templates.php';
            $email_subject = "Pagamento Confirmado - " . $inscricao['event_name'];
            $email_body = getEmailTemplatePagamentoConfirmado([
                'usuario_nome' => $inscricao['user_name'],
                'evento_nome' => $inscricao['event_name']
            ]);
            sendEmail($inscricao['user_email'], $email_subject, $email_body);
        } catch (Exception $emailEx) {
            error_log("[WEBHOOK] ⚠️ Falha ao enviar email para " . ($inscricao['user_email'] ?? '') . ": " . $emailEx->getMessage());
        }
    }

    // Remover da fila
    $queue = array_filter($queue, function($item) use ($payment_id) {
        return $item['payment_id'] !== $payment_id;
    });
    @file_put_contents($queue_file, json_encode(array_values($queue), JSON_PRETTY_PRINT));

} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    logInscricaoPagamento('ERROR', 'ERRO_WEBHOOK', [
        'payment_id' => (string)$payment_id,
        'external_reference' => $external_reference ?? null,
        'erro' => $e->getMessage()
    ]);
    
    error_log("[WEBHOOK] ❌ ERRO: " . $e->getMessage());
    @file_put_contents($log_file, date('Y-m-d H:i:s') . " [ERRO] " . $e->getMessage() . "\n", FILE_APPEND);
}
