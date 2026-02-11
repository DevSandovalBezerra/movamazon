<?php
/**
 * Helper de Cashback para Atletas
 * Sistema de cashback de 1% sobre o valor de inscrições (sem produtos extras)
 */

/**
 * Registra cashbacks pendentes para um usuário
 * Verifica inscrições pagas que ainda não têm cashback registrado
 * Função idempotente - pode ser chamada várias vezes sem duplicar registros
 * 
 * @param PDO $pdo Conexão com banco de dados
 * @param int $usuario_id ID do usuário
 * @return array Resultado com quantidade de cashbacks registrados
 */
function registrarCashbacksPendentes($pdo, $usuario_id) {
    $resultado = [
        'success' => true,
        'registrados' => 0,
        'erros' => []
    ];
    
    try {
        // Buscar inscrições pagas que ainda não têm cashback registrado
        // Usar valor_total ao invés de valor_modalidade (coluna não existe)
        $sql = "
            SELECT 
                i.id as inscricao_id,
                i.usuario_id,
                i.evento_id,
                i.valor_total,
                e.nome as evento_nome
            FROM inscricoes i
            INNER JOIN eventos e ON i.evento_id = e.id
            LEFT JOIN cashback_atletas ca ON ca.inscricao_id = i.id
            WHERE i.usuario_id = ?
            AND i.status_pagamento = 'pago'
            AND ca.id IS NULL
            AND i.valor_total > 0
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $inscricoesPendentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($inscricoesPendentes)) {
            return $resultado;
        }
        
        // Preparar insert
        $stmtInsert = $pdo->prepare("
            INSERT INTO cashback_atletas 
            (usuario_id, inscricao_id, evento_id, valor_inscricao, valor_cashback, percentual, status, observacao)
            VALUES (?, ?, ?, ?, ?, 1.00, 'disponivel', ?)
        ");
        
        foreach ($inscricoesPendentes as $inscricao) {
            try {
                $valorInscricao = (float) $inscricao['valor_total'];
                $valorCashback = round($valorInscricao * 0.01, 2); // 1% do valor
                
                if ($valorCashback > 0) {
                    $observacao = "Cashback automático - " . ($inscricao['evento_nome'] ?? 'Evento');
                    
                    $stmtInsert->execute([
                        $inscricao['usuario_id'],
                        $inscricao['inscricao_id'],
                        $inscricao['evento_id'],
                        $valorInscricao,
                        $valorCashback,
                        $observacao
                    ]);
                    
                    $resultado['registrados']++;
                }
            } catch (PDOException $e) {
                // Ignora erro de duplicidade (constraint UNIQUE)
                if ($e->getCode() != 23000) {
                    $resultado['erros'][] = "Inscrição {$inscricao['inscricao_id']}: " . $e->getMessage();
                    error_log("Erro ao registrar cashback para inscrição {$inscricao['inscricao_id']}: " . $e->getMessage());
                }
            }
        }
        
    } catch (Exception $e) {
        $resultado['success'] = false;
        $resultado['erros'][] = $e->getMessage();
        error_log("Erro geral ao registrar cashbacks: " . $e->getMessage());
    }
    
    return $resultado;
}

/**
 * Calcula o saldo total de cashback disponível para um usuário
 * 
 * @param PDO $pdo Conexão com banco de dados
 * @param int $usuario_id ID do usuário
 * @return float Saldo disponível
 */
function calcularSaldoCashback($pdo, $usuario_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(valor_cashback), 0) as saldo
            FROM cashback_atletas
            WHERE usuario_id = ?
            AND status = 'disponivel'
        ");
        $stmt->execute([$usuario_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (float) ($result['saldo'] ?? 0);
    } catch (Exception $e) {
        error_log("Erro ao calcular saldo de cashback: " . $e->getMessage());
        return 0;
    }
}

/**
 * Obtém o histórico completo de cashback de um usuário
 * 
 * @param PDO $pdo Conexão com banco de dados
 * @param int $usuario_id ID do usuário
 * @param string|null $status Filtrar por status (opcional)
 * @return array Lista de cashbacks
 */
function getHistoricoCashback($pdo, $usuario_id, $status = null) {
    try {
        $sql = "
            SELECT 
                ca.id,
                ca.inscricao_id,
                ca.evento_id,
                ca.valor_inscricao,
                ca.valor_cashback,
                ca.percentual,
                ca.status,
                ca.data_credito,
                ca.data_utilizacao,
                ca.inscricao_uso_id,
                ca.observacao,
                e.nome as evento_nome,
                e.data_inicio as evento_data
            FROM cashback_atletas ca
            INNER JOIN eventos e ON ca.evento_id = e.id
            WHERE ca.usuario_id = ?
        ";
        
        $params = [$usuario_id];
        
        if ($status) {
            $sql .= " AND ca.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY ca.data_credito DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Erro ao buscar histórico de cashback: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtém resumo de cashback de um usuário
 * 
 * @param PDO $pdo Conexão com banco de dados
 * @param int $usuario_id ID do usuário
 * @return array Resumo com totais por status
 */
function getResumoCashback($pdo, $usuario_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                status,
                COUNT(*) as quantidade,
                COALESCE(SUM(valor_cashback), 0) as total
            FROM cashback_atletas
            WHERE usuario_id = ?
            GROUP BY status
        ");
        $stmt->execute([$usuario_id]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $resumo = [
            'disponivel' => ['quantidade' => 0, 'total' => 0],
            'utilizado' => ['quantidade' => 0, 'total' => 0],
            'expirado' => ['quantidade' => 0, 'total' => 0],
            'pendente' => ['quantidade' => 0, 'total' => 0],
            'saldo_disponivel' => 0,
            'total_acumulado' => 0
        ];
        
        foreach ($resultados as $row) {
            $status = $row['status'];
            if (isset($resumo[$status])) {
                $resumo[$status]['quantidade'] = (int) $row['quantidade'];
                $resumo[$status]['total'] = (float) $row['total'];
            }
            $resumo['total_acumulado'] += (float) $row['total'];
        }
        
        $resumo['saldo_disponivel'] = $resumo['disponivel']['total'];
        
        return $resumo;
    } catch (Exception $e) {
        error_log("Erro ao buscar resumo de cashback: " . $e->getMessage());
        return [
            'disponivel' => ['quantidade' => 0, 'total' => 0],
            'utilizado' => ['quantidade' => 0, 'total' => 0],
            'expirado' => ['quantidade' => 0, 'total' => 0],
            'pendente' => ['quantidade' => 0, 'total' => 0],
            'saldo_disponivel' => 0,
            'total_acumulado' => 0
        ];
    }
}

