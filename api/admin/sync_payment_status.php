<?php
/**
 * API para sincronização manual de status de pagamento com Mercado Pago
 * Usado quando webhook falhou ou para verificação manual
 */

// Desabilitar exibição de erros para não quebrar JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../security_middleware.php';
require_once __DIR__ . '/../mercadolivre/payment_helper.php';
require_once __DIR__ . '/../helpers/inscricao_logger.php';
require_once __DIR__ . '/auth_middleware.php';

header('Content-Type: application/json; charset=utf-8');

// Verificar se é admin
requererAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit();
}

$dados = json_decode(file_get_contents('php://input'), true);
$inscricao_id = $dados['inscricao_id'] ?? null;
$payment_id = $dados['payment_id'] ?? null;

if (!$inscricao_id && !$payment_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'inscricao_id ou payment_id é obrigatório.']);
    exit();
}

try {
    $pdo->beginTransaction();
    
    // Buscar dados da inscrição e pagamento
    if ($inscricao_id) {
        $stmt = $pdo->prepare("
            SELECT i.*, pm.payment_id, pm.status as status_pagamento_ml, pm.inscricao_id
            FROM inscricoes i
            LEFT JOIN pagamentos_ml pm ON i.id = pm.inscricao_id
            WHERE i.id = ?
            ORDER BY pm.data_atualizacao DESC
            LIMIT 1
        ");
        $stmt->execute([$inscricao_id]);
        $inscricao = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$inscricao) {
            throw new Exception('Inscrição não encontrada');
        }
        
        $payment_id = $inscricao['payment_id'] ?? $inscricao['external_reference'] ?? null;
    } else {
        // Buscar por payment_id
        $stmt = $pdo->prepare("
            SELECT i.*, pm.payment_id, pm.status as status_pagamento_ml, pm.inscricao_id
            FROM pagamentos_ml pm
            JOIN inscricoes i ON pm.inscricao_id = i.id
            WHERE pm.payment_id = ?
            ORDER BY pm.data_atualizacao DESC
            LIMIT 1
        ");
        $stmt->execute([$payment_id]);
        $inscricao = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$inscricao) {
            throw new Exception('Pagamento não encontrado');
        }
        
        $inscricao_id = $inscricao['id'];
    }
    
    if (!$payment_id) {
        throw new Exception('Payment ID não encontrado para esta inscrição');
    }
    
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
    $status_anterior_ml = $inscricao['status_pagamento_ml'] ?? 'pendente';
    
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
    $stmt_check = $pdo->prepare("SELECT id FROM pagamentos_ml WHERE payment_id = ? LIMIT 1");
    $stmt_check->execute([$payment_id]);
    $pagamento_existente = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
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
    
    // Se pagamento aprovado, atualizar estoque de camisas
    if ($novoStatusInscricao === 'pago' && !empty($inscricao['tamanho_camiseta'])) {
        $stmt_estoque = $pdo->prepare("
            UPDATE camisas 
            SET quantidade_vendida = quantidade_vendida + 1, 
                quantidade_disponivel = quantidade_disponivel - 1 
            WHERE tamanho = ? AND evento_id = ?
        ");
        $stmt_estoque->execute([$inscricao['tamanho_camiseta'], $inscricao['evento_id']]);
    }
    
    $pdo->commit();
    
    // Log da operação
    logInscricaoPagamento('INFO', 'SYNC_PAYMENT_MANUAL', [
        'inscricao_id' => $inscricao_id,
        'payment_id' => $payment_id,
        'status_anterior_inscricao' => $status_anterior_inscricao,
        'status_novo_inscricao' => $novoStatusInscricao,
        'status_anterior_ml' => $status_anterior_ml,
        'status_novo_ml' => $novoStatusPagamentosML,
        'admin_id' => $_SESSION['user_id'] ?? null
    ]);
    
    // Se status mudou para pago, enviar email
    if ($novoStatusInscricao === 'pago' && $status_anterior_inscricao !== 'pago') {
        require_once __DIR__ . '/../helpers/email_helper.php';
        
        $stmt_user = $pdo->prepare("
            SELECT u.email, u.nome_completo, e.nome as evento_nome
            FROM usuarios u
            JOIN inscricoes i ON u.id = i.usuario_id
            JOIN eventos e ON i.evento_id = e.id
            WHERE i.id = ?
        ");
        $stmt_user->execute([$inscricao_id]);
        $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);
        
        if ($user_data) {
            require_once __DIR__ . '/../helpers/email_templates.php';
            
            $email_subject = "Pagamento Confirmado - Evento: " . $user_data['evento_nome'];
            $email_body = getEmailTemplatePagamentoConfirmado([
                'usuario_nome' => $user_data['nome_completo'],
                'evento_nome' => $user_data['evento_nome']
            ]);
            
            sendEmail($user_data['email'], $email_subject, $email_body);
        }
    }
    
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
    
    error_log("[SYNC_PAYMENT_STATUS] Erro: " . $e->getMessage());
    error_log("[SYNC_PAYMENT_STATUS] Stack trace: " . $e->getTraceAsString());
    
    if (function_exists('logInscricaoPagamento')) {
        logInscricaoPagamento('ERROR', 'SYNC_PAYMENT_MANUAL_ERROR', [
            'inscricao_id' => $inscricao_id ?? null,
            'payment_id' => $payment_id ?? null,
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
