<?php
/**
 * API para admin processar solicitações de cancelamento
 * Aprovar ou rejeitar cancelamentos e processar reembolsos
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
require_once __DIR__ . '/../helpers/email_helper.php';
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
$solicitacao_id = $dados['solicitacao_id'] ?? null;
$acao = $dados['acao'] ?? null; // 'aprovar' ou 'rejeitar'
$motivo_rejeicao = trim($dados['motivo_rejeicao'] ?? '');
$observacoes = trim($dados['observacoes'] ?? '');

if (!$solicitacao_id || !$acao) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'solicitacao_id e acao são obrigatórios.']);
    exit();
}

if (!in_array($acao, ['aprovar', 'rejeitar'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ação inválida. Use "aprovar" ou "rejeitar".']);
    exit();
}

if ($acao === 'rejeitar' && empty($motivo_rejeicao)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Motivo da rejeição é obrigatório.']);
    exit();
}

try {
    $pdo->beginTransaction();
    
    $admin_id = $_SESSION['user_id'] ?? null;
    
    // Buscar solicitação
    $stmt = $pdo->prepare("
        SELECT sc.*, i.*, e.nome as evento_nome, COALESCE(e.data_realizacao, e.data_inicio) as data_evento,
               u.email as usuario_email, u.nome_completo as usuario_nome,
               pm.payment_id, pm.status as status_pagamento_ml
        FROM solicitacoes_cancelamento sc
        JOIN inscricoes i ON sc.inscricao_id = i.id
        JOIN eventos e ON i.evento_id = e.id
        JOIN usuarios u ON sc.usuario_id = u.id
        LEFT JOIN pagamentos_ml pm ON i.id = pm.inscricao_id
        WHERE sc.id = ? AND sc.status = 'pendente'
        ORDER BY pm.data_atualizacao DESC
        LIMIT 1
    ");
    $stmt->execute([$solicitacao_id]);
    $solicitacao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$solicitacao) {
        throw new Exception('Solicitação não encontrada ou já processada.');
    }
    
    if ($acao === 'rejeitar') {
        // Rejeitar solicitação
        $stmt_update = $pdo->prepare("
            UPDATE solicitacoes_cancelamento SET
                status = 'rejeitada',
                motivo_rejeicao = ?,
                admin_id = ?,
                data_processamento = NOW(),
                observacoes = ?
            WHERE id = ?
        ");
        $stmt_update->execute([$motivo_rejeicao, $admin_id, $observacoes, $solicitacao_id]);
        
        // Log
        logInscricaoPagamento('INFO', 'CANCELAMENTO_REJEITADO', [
            'inscricao_id' => $solicitacao['inscricao_id'],
            'solicitacao_id' => $solicitacao_id,
            'admin_id' => $admin_id,
            'motivo_rejeicao' => $motivo_rejeicao
        ]);
        
        // Enviar email ao participante
        require_once __DIR__ . '/../helpers/email_templates.php';
        
        $email_subject = "Solicitação de Cancelamento Rejeitada - Evento: " . $solicitacao['evento_nome'];
        $email_body = getEmailTemplateCancelamentoRejeitado([
            'usuario_nome' => $solicitacao['usuario_nome'],
            'evento_nome' => $solicitacao['evento_nome'],
            'motivo_rejeicao' => $motivo_rejeicao
        ]);
        sendEmail($solicitacao['usuario_email'], $email_subject, $email_body);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Solicitação rejeitada com sucesso.'
        ]);
        
    } else {
        // Aprovar e processar cancelamento
        $inscricao_id = $solicitacao['inscricao_id'];
        $payment_id = $solicitacao['payment_id'];
        $valor_reembolso = null;
        $reembolso_processado = false;
        
        // Se tem payment_id e pagamento foi aprovado, processar reembolso
        if ($payment_id && $solicitacao['status_pagamento'] === 'pago') {
            try {
                $paymentHelper = new PaymentHelper();
                
                // Calcular valor do reembolso (total por padrão, pode ser parcial no futuro)
                $valor_reembolso = (float)$solicitacao['valor_total'];
                
                // Processar reembolso no Mercado Pago
                $refundData = $paymentHelper->processarReembolso($payment_id, $valor_reembolso);
                
                $reembolso_processado = true;
                
                logInscricaoPagamento('INFO', 'REEMBOLSO_PROCESSADO', [
                    'inscricao_id' => $inscricao_id,
                    'payment_id' => $payment_id,
                    'valor_reembolso' => $valor_reembolso,
                    'refund_id' => $refundData['id'] ?? null
                ]);
                
            } catch (Exception $e) {
                error_log("[PROCESSAR_CANCELAMENTO] Erro ao processar reembolso: " . $e->getMessage());
                // Continuar com cancelamento mesmo se reembolso falhar (pode ser processado manualmente depois)
                logInscricaoPagamento('WARNING', 'REEMBOLSO_FALHOU', [
                    'inscricao_id' => $inscricao_id,
                    'payment_id' => $payment_id,
                    'erro' => $e->getMessage()
                ]);
            }
        }
        
        // Atualizar inscrição
        $stmt_update_inscricao = $pdo->prepare("
            UPDATE inscricoes SET
                status = 'cancelada',
                status_pagamento = 'cancelado'
            WHERE id = ?
        ");
        $stmt_update_inscricao->execute([$inscricao_id]);
        
        // Atualizar pagamentos_ml
        if ($payment_id) {
            $stmt_update_pagamento = $pdo->prepare("
                UPDATE pagamentos_ml SET
                    status = 'cancelado',
                    data_atualizacao = NOW()
                WHERE payment_id = ?
            ");
            $stmt_update_pagamento->execute([$payment_id]);
        }
        
        // Liberar estoque de camisas (decrementar quantidade_vendida)
        if (!empty($solicitacao['tamanho_camiseta'])) {
            $stmt_estoque = $pdo->prepare("
                UPDATE camisas 
                SET quantidade_vendida = GREATEST(0, quantidade_vendida - 1),
                    quantidade_disponivel = quantidade_disponivel + 1
                WHERE tamanho = ? AND evento_id = ?
            ");
            $stmt_estoque->execute([$solicitacao['tamanho_camiseta'], $solicitacao['evento_id']]);
        }
        
        // Atualizar solicitação
        $stmt_update_solicitacao = $pdo->prepare("
            UPDATE solicitacoes_cancelamento SET
                status = 'processada',
                admin_id = ?,
                data_processamento = NOW(),
                valor_reembolso = ?,
                observacoes = ?
            WHERE id = ?
        ");
        $stmt_update_solicitacao->execute([
            $admin_id,
            $valor_reembolso,
            $observacoes,
            $solicitacao_id
        ]);
        
        // Log
        logInscricaoPagamento('INFO', 'CANCELAMENTO_APROVADO', [
            'inscricao_id' => $inscricao_id,
            'solicitacao_id' => $solicitacao_id,
            'admin_id' => $admin_id,
            'valor_reembolso' => $valor_reembolso,
            'reembolso_processado' => $reembolso_processado
        ]);
        
        // Enviar email ao participante
        require_once __DIR__ . '/../helpers/email_templates.php';
        
        $email_subject = "Cancelamento Aprovado - Evento: " . $solicitacao['evento_nome'];
        $email_body = getEmailTemplateCancelamentoAprovado([
            'usuario_nome' => $solicitacao['usuario_nome'],
            'evento_nome' => $solicitacao['evento_nome'],
            'reembolso_processado' => $reembolso_processado,
            'valor_reembolso' => $valor_reembolso,
            'status_pagamento' => $solicitacao['status_pagamento']
        ]);
        sendEmail($solicitacao['usuario_email'], $email_subject, $email_body);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Cancelamento processado com sucesso.',
            'data' => [
                'reembolso_processado' => $reembolso_processado,
                'valor_reembolso' => $valor_reembolso
            ]
        ]);
    }
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("[PROCESSAR_CANCELAMENTO] Erro: " . $e->getMessage());
    error_log("[PROCESSAR_CANCELAMENTO] Stack trace: " . $e->getTraceAsString());
    
    if (function_exists('logInscricaoPagamento')) {
        logInscricaoPagamento('ERROR', 'PROCESSAR_CANCELAMENTO_ERROR', [
            'solicitacao_id' => $solicitacao_id ?? null,
            'acao' => $acao ?? null,
            'erro' => $e->getMessage()
        ]);
    }
    
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar cancelamento: ' . $e->getMessage(),
        'error' => $e->getMessage()
    ]);
}
