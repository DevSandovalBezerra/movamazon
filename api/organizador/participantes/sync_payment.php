<?php
/**
 * API para sincronização manual de status de pagamento com Mercado Pago (para organizadores)
 * Valida se a inscrição pertence a um evento do organizador
 */

// Desabilitar exibição de erros para não quebrar JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';
require_once __DIR__ . '/../../mercadolivre/payment_helper.php';
require_once __DIR__ . '/../../helpers/inscricao_logger.php';

header('Content-Type: application/json; charset=utf-8');

// Verificar se é organizador
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado. Apenas organizadores podem sincronizar pagamentos.']);
    exit();
}

$ctx = requireOrganizadorContext($pdo);
$usuario_id = $ctx['usuario_id'];
$organizador_id = $ctx['organizador_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit();
}

$dados = json_decode(file_get_contents('php://input'), true);
$inscricao_id = $dados['inscricao_id'] ?? null;

if (!$inscricao_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'inscricao_id é obrigatório.']);
    exit();
}

try {
    // Verificar se a inscrição pertence a um evento do organizador e buscar payment_id
    $stmt_check = $pdo->prepare("
        SELECT i.*, e.organizador_id, e.id as evento_id, pm.payment_id
        FROM inscricoes i
        JOIN eventos e ON i.evento_id = e.id
        LEFT JOIN pagamentos_ml pm ON i.id = pm.inscricao_id
        WHERE i.id = ? AND (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL
        ORDER BY pm.data_atualizacao DESC
        LIMIT 1
    ");
    $stmt_check->execute([$inscricao_id, $organizador_id, $usuario_id]);
    $inscricao = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    if (!$inscricao) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Inscrição não encontrada ou não pertence a um evento seu.']);
        exit();
    }
    
    $payment_id = $inscricao['payment_id'] ?? $inscricao['external_reference'] ?? null;
    
    if (!$payment_id) {
        throw new Exception('Payment ID não encontrado para esta inscrição');
    }
    
    $pdo->beginTransaction();
    
    // Consultar status no Mercado Pago
    $paymentHelper = new PaymentHelper();
    $paymentData = $paymentHelper->consultarStatusPagamento($payment_id);
    
    $statusMP = $paymentData['status'] ?? 'pending';
    $novoStatusInscricao = PaymentHelper::mapearStatus($statusMP);
    $novoStatusPagamentosML = PaymentHelper::mapearStatusPagamentosML($statusMP);
    
    // Extrair dados do pagamento
    $valor_pago = (float)($paymentData['transaction_amount'] ?? 0);
    $metodo_pagamento = $paymentData['payment_method_id'] ?? $paymentData['payment_type_id'] ?? null;
    $parcelas = (int)($paymentData['installments'] ?? 1);
    $taxa_ml = 0.0;
    
    if (!empty($paymentData['fee_details']) && is_array($paymentData['fee_details'])) {
        foreach ($paymentData['fee_details'] as $fee) {
            if (isset($fee['amount'])) {
                $taxa_ml += (float)$fee['amount'];
            }
        }
    }
    
    $status_anterior_inscricao = $inscricao['status_pagamento'] ?? 'pendente';
    
    // Atualizar inscrição
    $novo_status = ($novoStatusInscricao === 'pago') ? 'confirmada' : $inscricao['status'];
    $data_pagamento = ($novoStatusInscricao === 'pago') ? date('Y-m-d H:i:s') : null;
    
    $stmt_update = $pdo->prepare("
        UPDATE inscricoes SET 
            status = ?,
            status_pagamento = ?, 
            data_pagamento = ?,
            forma_pagamento = COALESCE(forma_pagamento, 'mercadolivre')
        WHERE id = ?
    ");
    $stmt_update->execute([$novo_status, $novoStatusInscricao, $data_pagamento, $inscricao_id]);
    
    // Atualizar ou inserir em pagamentos_ml
    $stmt_check_pm = $pdo->prepare("SELECT id FROM pagamentos_ml WHERE payment_id = ? LIMIT 1");
    $stmt_check_pm->execute([$payment_id]);
    $pagamento_existente = $stmt_check_pm->fetch(PDO::FETCH_ASSOC);
    
    $dados_pagamento_json = json_encode($paymentData, JSON_UNESCAPED_UNICODE);
    
    if ($pagamento_existente) {
        $stmt_pagamentos_ml = $pdo->prepare("
            UPDATE pagamentos_ml SET 
                status = ?, 
                valor_pago = ?,
                metodo_pagamento = ?,
                parcelas = ?,
                taxa_ml = ?,
                dados_pagamento = ?,
                data_atualizacao = NOW()
            WHERE id = ?
        ");
        $stmt_pagamentos_ml->execute([
            $novoStatusPagamentosML,
            $valor_pago,
            $metodo_pagamento,
            $parcelas,
            $taxa_ml,
            $dados_pagamento_json,
            $pagamento_existente['id']
        ]);
    } else {
        $stmt_pagamentos_ml = $pdo->prepare("
            INSERT INTO pagamentos_ml (
                inscricao_id, payment_id, status,
                valor_pago, metodo_pagamento, parcelas, taxa_ml,
                dados_pagamento, data_criacao, data_atualizacao
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt_pagamentos_ml->execute([
            $inscricao_id,
            $payment_id,
            $novoStatusPagamentosML,
            $valor_pago,
            $metodo_pagamento,
            $parcelas,
            $taxa_ml,
            $dados_pagamento_json
        ]);
    }
    
    $pdo->commit();
    
    // Log da operação
    logInscricaoPagamento('INFO', 'SYNC_PAYMENT_MANUAL_ORGANIZADOR', [
        'inscricao_id' => $inscricao_id,
        'payment_id' => $payment_id,
        'status_anterior_inscricao' => $status_anterior_inscricao,
        'status_novo_inscricao' => $novoStatusInscricao,
        'organizador_id' => $organizador_id
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Status sincronizado com sucesso',
        'data' => [
            'inscricao_id' => $inscricao_id,
            'payment_id' => $payment_id,
            'status_anterior' => $status_anterior_inscricao,
            'status_novo' => $novoStatusInscricao,
            'status_mudou' => $status_anterior_inscricao !== $novoStatusInscricao
        ]
    ]);
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("[SYNC_PAYMENT_STATUS_ORGANIZADOR] Erro: " . $e->getMessage());
    error_log("[SYNC_PAYMENT_STATUS_ORGANIZADOR] Stack trace: " . $e->getTraceAsString());
    
    if (function_exists('logInscricaoPagamento')) {
        logInscricaoPagamento('ERROR', 'SYNC_PAYMENT_MANUAL_ORGANIZADOR_ERROR', [
            'inscricao_id' => $inscricao_id ?? null,
            'erro' => $e->getMessage()
        ]);
    }
    
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao sincronizar status: ' . $e->getMessage(),
        'error' => $e->getMessage()
    ]);
}
