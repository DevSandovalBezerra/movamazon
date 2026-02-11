<?php
/**
 * Helper para Cancelamento Automático de Inscrições Expiradas
 * 
 * Função reutilizável que pode ser chamada de qualquer lugar do sistema
 * para garantir que inscrições expiradas sejam canceladas.
 * 
 * Regras de cancelamento:
 * 1. Boletos expirados: data_expiracao_pagamento < NOW()
 * 2. Pendentes por mais de 72 horas: data_inscricao < NOW() - 72 HOURS
 * 3. Após data de encerramento: data_inscricao > evento.data_fim_inscricoes
 * 
 * @param PDO $pdo Conexão com banco de dados
 * @param bool $silent Se true, não loga detalhes (útil para fallbacks)
 * @return array Resultado do processamento
 */
function cancelarInscricoesExpiradas($pdo, $silent = false) {
    $cancelamentos = [];
    $agora = date('Y-m-d H:i:s');
    $data_72h_atras = date('Y-m-d H:i:s', strtotime('-72 hours'));
    
    if (!$silent) {
        error_log("[CANCELAR_INSCRICOES] Iniciando processamento em $agora");
    }
    
    try {
        $pdo->beginTransaction();
        
        // REGRA 1: Boletos expirados
        // IMPORTANTE: Não cancelar se status_pagamento = 'processando' (PIX em andamento)
        // Não cancelar se já está pago ou cancelado
        $sql_boleto = "
            UPDATE inscricoes i
            INNER JOIN eventos e ON i.evento_id = e.id
            SET i.status_pagamento = 'cancelado',
                i.status = 'cancelada'
            WHERE i.status_pagamento = 'pendente'
              AND i.forma_pagamento = 'boleto'
              AND i.data_expiracao_pagamento IS NOT NULL
              AND i.data_expiracao_pagamento < ?
              AND i.status != 'cancelada'
        ";
        
        $stmt_boleto = $pdo->prepare($sql_boleto);
        $stmt_boleto->execute([$agora]);
        $cancelados_boleto = $stmt_boleto->rowCount();
        
        if ($cancelados_boleto > 0) {
            $cancelamentos['boletos_expirados'] = $cancelados_boleto;
            if (!$silent) {
                error_log("[CANCELAR_INSCRICOES] ✅ Cancelados $cancelados_boleto boletos expirados");
            }
        }
        
        // REGRA 2: Pendentes por mais de 72 horas
        // IMPORTANTE: Não cancelar se status_pagamento = 'processando' (PIX em andamento)
        // Não cancelar se já está pago ou cancelado
        $sql_72h = "
            UPDATE inscricoes i
            INNER JOIN eventos e ON i.evento_id = e.id
            SET i.status_pagamento = 'cancelado',
                i.status = 'cancelada'
            WHERE i.status_pagamento = 'pendente'
              AND i.data_inscricao < ?
              AND i.status != 'cancelada'
        ";
        
        $stmt_72h = $pdo->prepare($sql_72h);
        $stmt_72h->execute([$data_72h_atras]);
        $cancelados_72h = $stmt_72h->rowCount();
        
        if ($cancelados_72h > 0) {
            $cancelamentos['pendentes_72h'] = $cancelados_72h;
            if (!$silent) {
                error_log("[CANCELAR_INSCRICOES] ✅ Canceladas $cancelados_72h inscrições pendentes há mais de 72h");
            }
        }
        
        // REGRA 3: Inscrições após data de encerramento
        // IMPORTANTE: Não cancelar se status_pagamento = 'processando' (PIX em andamento)
        // Não cancelar se já está pago ou cancelado
        $sql_encerramento = "
            UPDATE inscricoes i
            INNER JOIN eventos e ON i.evento_id = e.id
            SET i.status_pagamento = 'cancelado',
                i.status = 'cancelada'
            WHERE i.status_pagamento = 'pendente'
              AND e.data_fim_inscricoes IS NOT NULL
              AND i.data_inscricao > CONCAT(e.data_fim_inscricoes, ' ', COALESCE(e.hora_fim_inscricoes, '23:59:59'))
              AND i.status != 'cancelada'
        ";
        
        $stmt_encerramento = $pdo->prepare($sql_encerramento);
        $stmt_encerramento->execute();
        $cancelados_encerramento = $stmt_encerramento->rowCount();
        
        if ($cancelados_encerramento > 0) {
            $cancelamentos['apos_encerramento'] = $cancelados_encerramento;
            if (!$silent) {
                error_log("[CANCELAR_INSCRICOES] ✅ Canceladas $cancelados_encerramento inscrições após encerramento");
            }
        }
        
        $pdo->commit();
        
        $total = array_sum($cancelamentos);
        
        if (!$silent && $total > 0) {
            error_log("[CANCELAR_INSCRICOES] ✅ Total de $total inscrições canceladas");
            error_log("[CANCELAR_INSCRICOES] Detalhes: " . json_encode($cancelamentos));
        }
        
        return [
            'success' => true,
            'message' => "Processamento concluído. $total inscrição(ões) cancelada(s).",
            'cancelamentos' => $cancelamentos,
            'total' => $total,
            'timestamp' => $agora
        ];
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        $error_msg = $e->getMessage();
        if (!$silent) {
            error_log("[CANCELAR_INSCRICOES] ❌ Erro: " . $error_msg);
            error_log("[CANCELAR_INSCRICOES] Stack trace: " . $e->getTraceAsString());
        }
        
        return [
            'success' => false,
            'message' => 'Erro ao processar cancelamentos',
            'error' => $error_msg,
            'timestamp' => $agora
        ];
    }
}
