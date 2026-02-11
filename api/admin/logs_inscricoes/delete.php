<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth_middleware.php';
require_once __DIR__ . '/../../db.php';

header('Content-Type: application/json');

if (!requererAdmin(false)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit();
}

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $preview = isset($input['preview']) && $input['preview'] === true;
    $tipo = $input['tipo'] ?? null;
    
    // Construir condições WHERE
    $where_conditions = [];
    $params = [];
    $detalhes = [];
    
    switch ($tipo) {
        case 'todos':
            // Deletar todos - requer confirmação explícita
            if (!isset($input['confirmar']) || $input['confirmar'] !== true) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Confirmação explícita necessária para deletar todos os logs'
                ]);
                exit();
            }
            // Não adicionar WHERE - deleta tudo
            break;
            
        case 'periodo':
            // Deletar logs mais antigos que X dias
            if (!isset($input['periodo_dias']) || !is_numeric($input['periodo_dias'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Período em dias não informado']);
                exit();
            }
            $dias = intval($input['periodo_dias']);
            $where_conditions[] = "created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $params[] = $dias;
            $detalhes['periodo_dias'] = $dias;
            break;
            
        case 'nivel':
            // Deletar por nível
            if (!isset($input['nivel']) || !in_array($input['nivel'], ['ERROR', 'WARNING', 'INFO', 'SUCCESS'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Nível inválido']);
                exit();
            }
            $where_conditions[] = "nivel = ?";
            $params[] = $input['nivel'];
            $detalhes['nivel'] = $input['nivel'];
            break;
            
        case 'acao':
            // Deletar por ação
            if (!isset($input['acao']) || empty($input['acao'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Ação não informada']);
                exit();
            }
            $where_conditions[] = "acao = ?";
            $params[] = $input['acao'];
            $detalhes['acao'] = $input['acao'];
            break;
            
        case 'inscricao':
            // Deletar logs de uma inscrição específica
            if (!isset($input['inscricao_id']) || !is_numeric($input['inscricao_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID da inscrição não informado']);
                exit();
            }
            $where_conditions[] = "inscricao_id = ?";
            $params[] = intval($input['inscricao_id']);
            $detalhes['inscricao_id'] = intval($input['inscricao_id']);
            break;
            
        case 'manter_ultimos':
            // Manter apenas últimos N dias
            if (!isset($input['manter_ultimos_dias']) || !is_numeric($input['manter_ultimos_dias'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Número de dias não informado']);
                exit();
            }
            $dias = intval($input['manter_ultimos_dias']);
            $where_conditions[] = "created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $params[] = $dias;
            $detalhes['manter_ultimos_dias'] = $dias;
            break;
            
        case 'periodo_especifico':
            // Deletar logs em um período específico
            if (!isset($input['data_inicio']) || !isset($input['data_fim'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Data início ou fim não informada']);
                exit();
            }
            $where_conditions[] = "DATE(created_at) >= ?";
            $where_conditions[] = "DATE(created_at) <= ?";
            $params[] = $input['data_inicio'];
            $params[] = $input['data_fim'];
            $detalhes['data_inicio'] = $input['data_inicio'];
            $detalhes['data_fim'] = $input['data_fim'];
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Tipo de limpeza não especificado ou inválido']);
            exit();
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Preview: contar registros que serão deletados
    if ($preview) {
        // Contar total
        $sql_count = "SELECT COUNT(*) as total FROM logs_inscricoes_pagamentos $where_clause";
        $stmt_count = $pdo->prepare($sql_count);
        $stmt_count->execute($params);
        $total = (int)$stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Contar por nível
        $sql_nivel = "SELECT nivel, COUNT(*) as total FROM logs_inscricoes_pagamentos $where_clause GROUP BY nivel";
        $stmt_nivel = $pdo->prepare($sql_nivel);
        $stmt_nivel->execute($params);
        $por_nivel = [];
        while ($row = $stmt_nivel->fetch(PDO::FETCH_ASSOC)) {
            $por_nivel[$row['nivel']] = (int)$row['total'];
        }
        
        // Período dos registros
        $sql_periodo = "SELECT 
            MIN(created_at) as mais_antigo,
            MAX(created_at) as mais_recente
        FROM logs_inscricoes_pagamentos $where_clause";
        $stmt_periodo = $pdo->prepare($sql_periodo);
        $stmt_periodo->execute($params);
        $periodo = $stmt_periodo->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'preview' => true,
            'registros_afetados' => $total,
            'detalhes' => [
                'por_nivel' => $por_nivel,
                'periodo' => [
                    'mais_antigo' => $periodo['mais_antigo'] ?? null,
                    'mais_recente' => $periodo['mais_recente'] ?? null
                ],
                'tipo' => $tipo,
                'parametros' => $detalhes
            ]
        ]);
        exit();
    }
    
    // Executar deleção
    $pdo->beginTransaction();
    
    try {
        // Limite de segurança: máximo 10.000 registros por vez
        $sql_count = "SELECT COUNT(*) as total FROM logs_inscricoes_pagamentos $where_clause";
        $stmt_count = $pdo->prepare($sql_count);
        $stmt_count->execute($params);
        $total = (int)$stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
        
        if ($total > 10000) {
            throw new Exception("Operação muito grande. Máximo de 10.000 registros por vez. Use filtros mais específicos.");
        }
        
        // Deletar
        $sql_delete = "DELETE FROM logs_inscricoes_pagamentos $where_clause";
        $stmt_delete = $pdo->prepare($sql_delete);
        $stmt_delete->execute($params);
        $registros_deletados = $stmt_delete->rowCount();
        
        // Registrar ação de limpeza (log em arquivo)
        $admin_id = $_SESSION['user_id'] ?? 'unknown';
        $admin_email = $_SESSION['user_email'] ?? 'unknown';
        $log_message = sprintf(
            "[%s] Limpeza de logs executada por admin ID %s (%s) - Tipo: %s - Registros deletados: %d",
            date('Y-m-d H:i:s'),
            $admin_id,
            $admin_email,
            $tipo,
            $registros_deletados
        );
        error_log($log_message);
        
        // Salvar em arquivo de log de ações administrativas
        $log_file = dirname(dirname(dirname(__DIR__))) . '/logs/admin_actions.log';
        $log_dir = dirname($log_file);
        if (!is_dir($log_dir)) {
            @mkdir($log_dir, 0755, true);
        }
        @file_put_contents($log_file, $log_message . PHP_EOL, FILE_APPEND | LOCK_EX);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'preview' => false,
            'registros_deletados' => $registros_deletados,
            'mensagem' => "$registros_deletados registro(s) deletado(s) com sucesso"
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao deletar logs: ' . $e->getMessage()
    ]);
    error_log('[LOGS_INSCRICOES] Erro ao deletar: ' . $e->getMessage());
}

