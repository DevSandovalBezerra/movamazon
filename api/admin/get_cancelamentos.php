<?php
/**
 * API para listar solicitações de cancelamento (para admin)
 */

// Desabilitar exibição de erros para não quebrar JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/auth_middleware.php';

header('Content-Type: application/json; charset=utf-8');

requererAdmin();

try {
    $status = isset($_GET['status']) ? $_GET['status'] : 'pendente';
    $evento_id = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : null;
    $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 50;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    // Verificar se tabela existe antes de consultar
    $check_table = $pdo->query("
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.TABLES 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'solicitacoes_cancelamento'
    ")->fetchColumn();
    
    if ($check_table == 0) {
        // Tabela não existe, retornar dados vazios
        echo json_encode([
            'success' => true,
            'data' => [],
            'pagination' => [
                'total' => 0,
                'limit' => $limite,
                'offset' => $offset,
                'has_more' => false
            ],
            'stats' => [
                'total' => 0,
                'pendentes' => 0,
                'aprovadas' => 0,
                'rejeitadas' => 0,
                'processadas' => 0
            ]
        ]);
        exit();
    }
    
    $where_conditions = [];
    $params = [];
    
    if ($status !== 'todos') {
        $where_conditions[] = "sc.status = ?";
        $params[] = $status;
    }
    
    if ($evento_id) {
        $where_conditions[] = "i.evento_id = ?";
        $params[] = $evento_id;
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Contar total
    $sql_count = "
        SELECT COUNT(*) as total
        FROM solicitacoes_cancelamento sc
        JOIN inscricoes i ON sc.inscricao_id = i.id
        $where_clause
    ";
    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute($params);
    $total = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Buscar dados
    // LIMIT e OFFSET não podem ser placeholders, usar valores diretos (já validados como int)
    $sql = "
        SELECT 
            sc.*,
            i.evento_id,
            i.valor_total,
            i.status as status_inscricao,
            i.status_pagamento,
            u.nome_completo as usuario_nome,
            u.email as usuario_email,
            e.nome as evento_nome,
            COALESCE(e.data_realizacao, e.data_inicio) as data_evento,
            admin.nome_completo as admin_nome,
            DATEDIFF(COALESCE(e.data_realizacao, e.data_inicio), NOW()) as dias_ate_evento
        FROM solicitacoes_cancelamento sc
        JOIN inscricoes i ON sc.inscricao_id = i.id
        JOIN usuarios u ON sc.usuario_id = u.id
        JOIN eventos e ON i.evento_id = e.id
        LEFT JOIN usuarios admin ON sc.admin_id = admin.id
        $where_clause
        ORDER BY sc.data_solicitacao DESC
        LIMIT " . (int)$limite . " OFFSET " . (int)$offset . "
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $cancelamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Estatísticas
    $sql_stats = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as pendentes,
            SUM(CASE WHEN status = 'aprovada' THEN 1 ELSE 0 END) as aprovadas,
            SUM(CASE WHEN status = 'rejeitada' THEN 1 ELSE 0 END) as rejeitadas,
            SUM(CASE WHEN status = 'processada' THEN 1 ELSE 0 END) as processadas
        FROM solicitacoes_cancelamento
    ";
    $stmt_stats = $pdo->prepare($sql_stats);
    $stmt_stats->execute();
    $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $cancelamentos,
        'pagination' => [
            'total' => (int)$total,
            'limit' => $limite,
            'offset' => $offset,
            'has_more' => ($offset + $limite) < $total
        ],
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    error_log("[GET_CANCELAMENTOS] Erro: " . $e->getMessage());
    error_log("[GET_CANCELAMENTOS] Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar cancelamentos.',
        'error' => $e->getMessage()
    ]);
}
