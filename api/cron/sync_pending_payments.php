<?php
/**
 * Job automático para sincronizar pagamentos pendentes
 * Executar via cron a cada 1 hora: 0 * * * * /usr/bin/php /caminho/para/api/cron/sync_pending_payments.php
 */

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../mercadolivre/payment_helper.php';
require_once __DIR__ . '/../helpers/inscricao_logger.php';
require_once __DIR__ . '/../helpers/email_helper.php';

// Configurações
$horas_minimas = 2; // Sincronizar pagamentos pendentes há mais de 2 horas
$limite_tentativas = 3; // Máximo de tentativas por inscrição
$taxa_falha_alerta = 30; // Alertar se taxa de falha > 30%

try {
    error_log("[SYNC_PENDING_PAYMENTS] Iniciando sincronização automática em " . date('Y-m-d H:i:s'));
    
    // Buscar inscrições pendentes há mais de X horas
    $stmt = $pdo->prepare("
        SELECT 
            i.id as inscricao_id,
            i.external_reference,
            pm.payment_id,
            pm.id as pagamento_ml_id,
            COUNT(logs.id) as tentativas_anteriores
        FROM inscricoes i
        LEFT JOIN pagamentos_ml pm ON i.id = pm.inscricao_id
        LEFT JOIN logs_inscricoes logs ON logs.inscricao_id = i.id 
            AND logs.acao = 'SYNC_PAYMENT_AUTO'
            AND logs.data_criacao >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        WHERE i.status_pagamento = 'pendente'
        AND TIMESTAMPDIFF(HOUR, i.data_inscricao, NOW()) >= ?
        GROUP BY i.id, pm.payment_id, pm.id
        HAVING tentativas_anteriores < ?
        LIMIT 100
    ");
    
    $stmt->execute([$horas_minimas, $limite_tentativas]);
    $inscricoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total = count($inscricoes);
    $sucessos = 0;
    $falhas = 0;
    $atualizados = 0;
    
    error_log("[SYNC_PENDING_PAYMENTS] Encontradas $total inscrições para sincronizar");
    
    if ($total === 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Nenhuma inscrição pendente encontrada para sincronizar.',
            'total' => 0
        ]);
        exit;
    }
    
    $paymentHelper = new PaymentHelper();
    
    foreach ($inscricoes as $inscricao) {
        try {
            $inscricao_id = $inscricao['inscricao_id'];
            $payment_id = $inscricao['payment_id'] ?? $inscricao['external_reference'] ?? null;
            
            if (!$payment_id) {
                error_log("[SYNC_PENDING_PAYMENTS] Inscrição $inscricao_id sem payment_id, pulando...");
                $falhas++;
                continue;
            }
            
            // Consultar status no Mercado Pago
            $paymentData = $paymentHelper->consultarStatusPagamento($payment_id);
            $statusMP = $paymentData['status'] ?? 'pending';
            $novoStatusInscricao = PaymentHelper::mapearStatus($statusMP);
            $novoStatusPagamentosML = PaymentHelper::mapearStatusPagamentosML($statusMP);
            
            // Buscar status atual
            $stmt_atual = $pdo->prepare("
                SELECT status_pagamento FROM inscricoes WHERE id = ?
            ");
            $stmt_atual->execute([$inscricao_id]);
            $status_atual = $stmt_atual->fetchColumn();
            
            // Se status mudou, atualizar
            if ($status_atual !== $novoStatusInscricao) {
                $pdo->beginTransaction();
                
                // Atualizar inscrição
                $novo_status = ($novoStatusInscricao === 'pago') ? 'confirmada' : 'ativa';
                $data_pagamento = ($novoStatusInscricao === 'pago') ? date('Y-m-d H:i:s') : null;
                
                $stmt_update = $pdo->prepare("
                    UPDATE inscricoes SET 
                        status = ?,
                        status_pagamento = ?,
                        data_pagamento = ?
                    WHERE id = ?
                ");
                $stmt_update->execute([$novo_status, $novoStatusInscricao, $data_pagamento, $inscricao_id]);
                
                // Atualizar pagamentos_ml
                if ($inscricao['pagamento_ml_id']) {
                    $stmt_update_ml = $pdo->prepare("
                        UPDATE pagamentos_ml SET 
                            status = ?,
                            data_atualizacao = NOW()
                        WHERE id = ?
                    ");
                    $stmt_update_ml->execute([$novoStatusPagamentosML, $inscricao['pagamento_ml_id']]);
                }
                
                $pdo->commit();
                
                // Log
                logInscricaoPagamento('INFO', 'SYNC_PAYMENT_AUTO', [
                    'inscricao_id' => $inscricao_id,
                    'payment_id' => $payment_id,
                    'status_anterior' => $status_atual,
                    'status_novo' => $novoStatusInscricao
                ]);
                
                $atualizados++;
                $sucessos++;
                
                // Se pagamento aprovado, enviar email
                if ($novoStatusInscricao === 'pago') {
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
            } else {
                $sucessos++; // Status já está correto
            }
            
        } catch (Exception $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
            error_log("[SYNC_PENDING_PAYMENTS] Erro ao sincronizar inscrição {$inscricao['inscricao_id']}: " . $e->getMessage());
            
            logInscricaoPagamento('ERROR', 'SYNC_PAYMENT_AUTO_ERROR', [
                'inscricao_id' => $inscricao['inscricao_id'] ?? null,
                'payment_id' => $payment_id ?? null,
                'erro' => $e->getMessage()
            ]);
            
            $falhas++;
        }
    }
    
    $taxa_falha = $total > 0 ? ($falhas / $total) * 100 : 0;
    
    // Alertar se taxa de falha alta
    if ($taxa_falha > $taxa_falha_alerta && $total >= 10) {
        $admin_emails = [];
        $stmt_admins = $pdo->prepare("
            SELECT email FROM usuarios 
            WHERE papel = 'admin' AND status = 'ativo'
        ");
        $stmt_admins->execute();
        while ($admin = $stmt_admins->fetch(PDO::FETCH_ASSOC)) {
            $admin_emails[] = $admin['email'];
        }
        
        if (!empty($admin_emails)) {
            require_once __DIR__ . '/../helpers/email_templates.php';
            
            $email_subject = "Alerta: Alta Taxa de Falha na Sincronização de Pagamentos";
            $email_body = getEmailTemplateAlertaSincronizacao([
                'total' => $total,
                'sucessos' => $sucessos,
                'falhas' => $falhas,
                'taxa_falha' => $taxa_falha,
                'atualizados' => $atualizados
            ]);
            
            foreach ($admin_emails as $admin_email) {
                sendEmail($admin_email, $email_subject, $email_body);
            }
        }
    }
    
    error_log("[SYNC_PENDING_PAYMENTS] Sincronização concluída: $sucessos sucessos, $falhas falhas, $atualizados atualizados");
    
    echo json_encode([
        'success' => true,
        'message' => "Sincronização concluída: $sucessos sucessos, $falhas falhas, $atualizados atualizados",
        'total' => $total,
        'sucessos' => $sucessos,
        'falhas' => $falhas,
        'atualizados' => $atualizados,
        'taxa_falha' => round($taxa_falha, 2)
    ]);
    
} catch (Exception $e) {
    error_log("[SYNC_PENDING_PAYMENTS] Erro fatal: " . $e->getMessage());
    error_log("[SYNC_PENDING_PAYMENTS] Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar sincronização: ' . $e->getMessage()
    ]);
}
