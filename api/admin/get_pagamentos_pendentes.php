<?php
/**
 * API para listar pagamentos pendentes (para admin)
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
    $horas_minimas = isset($_GET['horas_minimas']) ? (int)$_GET['horas_minimas'] : 24;
    $evento_id = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : null;
    $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 50;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    $where_conditions = ["i.status_pagamento = 'pendente'"];
    $params = [];
    
    // Filtrar por horas mínimas (inscrições pendentes há mais de X horas)
    $where_conditions[] = "TIMESTAMPDIFF(HOUR, i.data_inscricao, NOW()) >= ?";
    $params[] = $horas_minimas;
    
    if ($evento_id) {
        $where_conditions[] = "i.evento_id = ?";
        $params[] = $evento_id;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Contar total
    $sql_count = "
        SELECT COUNT(*) as total
        FROM inscricoes i
        WHERE $where_clause
    ";
    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute($params);
    $total = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Buscar dados
    // LIMIT e OFFSET não podem ser placeholders, usar valores diretos (já validados como int)
    $sql = "
        SELECT 
            i.id as inscricao_id,
            i.usuario_id,
            i.evento_id,
            i.status,
            i.status_pagamento,
            i.valor_total,
            i.data_inscricao,
            i.external_reference,
            u.nome_completo as usuario_nome,
            u.email as usuario_email,
            e.nome as evento_nome,
            COALESCE(e.data_realizacao, e.data_inicio) as data_evento,
            pm.payment_id,
            pm.status as status_pagamento_ml,
            pm.data_atualizacao as ultima_atualizacao_pagamento,
            TIMESTAMPDIFF(HOUR, i.data_inscricao, NOW()) as horas_pendente
        FROM inscricoes i
        JOIN usuarios u ON i.usuario_id = u.id
        JOIN eventos e ON i.evento_id = e.id
        LEFT JOIN pagamentos_ml pm ON i.id = pm.inscricao_id
        WHERE $where_clause
        ORDER BY i.data_inscricao ASC
        LIMIT " . (int)$limite . " OFFSET " . (int)$offset . "
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pagamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Estatísticas
    $sql_stats = "
        SELECT 
            COUNT(*) as total_pendentes,
            SUM(CASE WHEN TIMESTAMPDIFF(HOUR, i.data_inscricao, NOW()) >= 24 THEN 1 ELSE 0 END) as pendentes_24h,
            SUM(CASE WHEN TIMESTAMPDIFF(HOUR, i.data_inscricao, NOW()) >= 72 THEN 1 ELSE 0 END) as pendentes_72h,
            SUM(i.valor_total) as valor_total_pendente
        FROM inscricoes i
        WHERE i.status_pagamento = 'pendente'
    ";
    $stmt_stats = $pdo->prepare($sql_stats);
    $stmt_stats->execute();
    $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $pagamentos,
        'pagination' => [
            'total' => (int)$total,
            'limit' => $limite,
            'offset' => $offset,
            'has_more' => ($offset + $limite) < $total
        ],
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    error_log("[GET_PAGAMENTOS_PENDENTES] Erro: " . $e->getMessage());
    error_log("[GET_PAGAMENTOS_PENDENTES] Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar pagamentos pendentes.',
        'error' => $e->getMessage()
    ]);
}
