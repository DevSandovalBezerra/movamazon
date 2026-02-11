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

try {
    // Verificar se a tabela existe
    $check_table = $pdo->query("SHOW TABLES LIKE 'logs_inscricoes_pagamentos'");
    if ($check_table->rowCount() === 0) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Tabela logs_inscricoes_pagamentos não existe. Execute a migration create_logs_inscricoes_pagamentos.sql'
        ]);
        exit();
    }
    
    // Parâmetros de paginação
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $per_page = isset($_GET['per_page']) ? min(100, max(10, intval($_GET['per_page']))) : 20;
    $offset = ($page - 1) * $per_page;
    
    // Filtros
    $filtros = [];
    $params = [];
    
    // Filtro por nível
    if (isset($_GET['nivel']) && in_array($_GET['nivel'], ['ERROR', 'WARNING', 'INFO', 'SUCCESS'])) {
        $filtros[] = "l.nivel = ?";
        $params[] = $_GET['nivel'];
    }
    
    // Filtro por ação
    if (isset($_GET['acao']) && !empty($_GET['acao'])) {
        $filtros[] = "l.acao = ?";
        $params[] = $_GET['acao'];
    }
    
    // Filtro por inscrição
    if (isset($_GET['inscricao_id']) && is_numeric($_GET['inscricao_id'])) {
        $filtros[] = "l.inscricao_id = ?";
        $params[] = intval($_GET['inscricao_id']);
    }
    
    // Filtro por payment_id
    if (isset($_GET['payment_id']) && !empty($_GET['payment_id'])) {
        $filtros[] = "l.payment_id = ?";
        $params[] = $_GET['payment_id'];
    }
    
    // Filtro por período
    if (isset($_GET['data_inicio']) && !empty($_GET['data_inicio'])) {
        $filtros[] = "DATE(l.created_at) >= ?";
        $params[] = $_GET['data_inicio'];
    }
    
    if (isset($_GET['data_fim']) && !empty($_GET['data_fim'])) {
        $filtros[] = "DATE(l.created_at) <= ?";
        $params[] = $_GET['data_fim'];
    }
    
    // Filtro por busca textual
    if (isset($_GET['busca']) && !empty($_GET['busca'])) {
        $filtros[] = "(l.mensagem LIKE ? OR l.acao LIKE ?)";
        $search_term = '%' . $_GET['busca'] . '%';
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    // Filtro por status de pagamento
    if (isset($_GET['status_pagamento']) && !empty($_GET['status_pagamento'])) {
        $filtros[] = "l.status_pagamento = ?";
        $params[] = $_GET['status_pagamento'];
    }
    
    // Construir WHERE clause
    $where_clause = !empty($filtros) ? 'WHERE ' . implode(' AND ', $filtros) : '';
    
    // Query para contar total
    $sql_count = "SELECT COUNT(*) as total FROM logs_inscricoes_pagamentos" . ($where_clause ? " l $where_clause" : "");
    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute($params);
    $total = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Query para buscar logs com dados relacionados
    // Tentar adicionar JOINs se as tabelas existirem
    $joins = [];
    $selects = ['l.*'];
    
    try {
        $check_eventos = $pdo->query("SHOW TABLES LIKE 'eventos'")->rowCount();
        $check_usuarios = $pdo->query("SHOW TABLES LIKE 'usuarios'")->rowCount();
        $check_modalidades = $pdo->query("SHOW TABLES LIKE 'modalidades_eventos'")->rowCount();
        
        if ($check_eventos > 0) {
            $joins[] = "LEFT JOIN eventos e ON e.id = l.evento_id";
            $selects[] = "e.nome as evento_nome";
        }
        if ($check_usuarios > 0) {
            $joins[] = "LEFT JOIN usuarios u ON u.id = l.usuario_id";
            $selects[] = "u.nome_completo as usuario_nome";
            $selects[] = "u.email as usuario_email";
        }
        if ($check_modalidades > 0) {
            $joins[] = "LEFT JOIN modalidades_eventos m ON m.id = l.modalidade_id";
            $selects[] = "m.nome as modalidade_nome";
        }
    } catch (Exception $e) {
        // Se houver erro ao verificar tabelas, usar query simples sem JOINs
        error_log('[LOGS_INSCRICOES] Erro ao verificar tabelas para JOIN: ' . $e->getMessage());
    }
    
    // Garantir que LIMIT e OFFSET sejam inteiros
    $per_page = (int)$per_page;
    $offset = (int)$offset;
    
    $sql = "
        SELECT 
            " . implode(', ', $selects) . "
        FROM logs_inscricoes_pagamentos l
        " . (!empty($joins) ? implode(' ', $joins) : '') . "
        $where_clause
        ORDER BY l.created_at DESC
        LIMIT $per_page OFFSET $offset
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Processar dados_contexto JSON
    foreach ($logs as &$log) {
        if (!empty($log['dados_contexto'])) {
            $log['dados_contexto'] = json_decode($log['dados_contexto'], true);
        } else {
            $log['dados_contexto'] = [];
        }
        
        // Formatar data
        $log['created_at_formatted'] = date('d/m/Y H:i:s', strtotime($log['created_at']));
    }
    
    // Estatísticas por nível (sem filtros de paginação)
    $sql_stats = "SELECT 
        nivel,
        COUNT(*) as total
    FROM logs_inscricoes_pagamentos" . ($where_clause ? " l $where_clause" : "") . "
    GROUP BY nivel";
    
    // Remover LIMIT e OFFSET dos parâmetros para a query de stats
    $params_stats = $params;
    if (count($params_stats) >= 2) {
        $params_stats = array_slice($params_stats, 0, -2);
    }
    $stmt_stats = $pdo->prepare($sql_stats);
    $stmt_stats->execute($params_stats);
    $stats_raw = $stmt_stats->fetchAll(PDO::FETCH_ASSOC);
    
    $stats = [
        'total_errors' => 0,
        'total_warnings' => 0,
        'total_info' => 0,
        'total_success' => 0
    ];
    
    foreach ($stats_raw as $stat) {
        switch ($stat['nivel']) {
            case 'ERROR':
                $stats['total_errors'] = (int)$stat['total'];
                break;
            case 'WARNING':
                $stats['total_warnings'] = (int)$stat['total'];
                break;
            case 'INFO':
                $stats['total_info'] = (int)$stat['total'];
                break;
            case 'SUCCESS':
                $stats['total_success'] = (int)$stat['total'];
                break;
        }
    }
    
    echo json_encode([
        'success' => true,
        'logs' => $logs,
        'pagination' => [
            'total' => (int)$total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ],
        'stats' => $stats
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    $error_message = 'Erro ao buscar logs: ' . $e->getMessage();
    error_log('[LOGS_INSCRICOES] Erro PDO ao listar: ' . $e->getMessage());
    error_log('[LOGS_INSCRICOES] SQL State: ' . $e->getCode());
    echo json_encode([
        'success' => false,
        'message' => $error_message,
        'error_code' => $e->getCode(),
        'sql_state' => $e->errorInfo[0] ?? null
    ]);
} catch (Exception $e) {
    http_response_code(500);
    $error_message = 'Erro ao buscar logs: ' . $e->getMessage();
    error_log('[LOGS_INSCRICOES] Erro ao listar: ' . $e->getMessage());
    error_log('[LOGS_INSCRICOES] Stack trace: ' . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => $error_message
    ]);
}

