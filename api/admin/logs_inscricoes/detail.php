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
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do log não informado']);
        exit();
    }
    
    $log_id = intval($_GET['id']);
    
    // Buscar log específico
    // Verificar se as tabelas existem antes de fazer JOINs
    $joins = [];
    $selects = ['l.*'];
    
    try {
        $check_eventos = $pdo->query("SHOW TABLES LIKE 'eventos'")->rowCount();
        $check_usuarios = $pdo->query("SHOW TABLES LIKE 'usuarios'")->rowCount();
        $check_modalidades = $pdo->query("SHOW TABLES LIKE 'modalidades_eventos'")->rowCount();
        
        if ($check_eventos > 0) {
            $joins[] = "LEFT JOIN eventos e ON e.id = l.evento_id";
            $selects[] = "e.nome as evento_nome";
            $selects[] = "e.id as evento_id";
        }
        if ($check_usuarios > 0) {
            $joins[] = "LEFT JOIN usuarios u ON u.id = l.usuario_id";
            $selects[] = "u.nome_completo as usuario_nome";
            $selects[] = "u.email as usuario_email";
            $selects[] = "u.id as usuario_id";
        }
        if ($check_modalidades > 0) {
            $joins[] = "LEFT JOIN modalidades_eventos m ON m.id = l.modalidade_id";
            $selects[] = "m.nome as modalidade_nome";
            $selects[] = "m.id as modalidade_id";
        }
    } catch (Exception $e) {
        error_log('[LOGS_INSCRICOES] Erro ao verificar tabelas para JOIN: ' . $e->getMessage());
    }
    
    $sql = "
        SELECT 
            " . implode(', ', $selects) . "
        FROM logs_inscricoes_pagamentos l
        " . (!empty($joins) ? implode(' ', $joins) : '') . "
        WHERE l.id = ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$log_id]);
    $log = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$log) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Log não encontrado']);
        exit();
    }
    
    // Processar dados_contexto JSON
    if (!empty($log['dados_contexto'])) {
        $log['dados_contexto'] = json_decode($log['dados_contexto'], true);
    } else {
        $log['dados_contexto'] = [];
    }
    
    // Formatar data
    $log['created_at_formatted'] = date('d/m/Y H:i:s', strtotime($log['created_at']));
    
    // Buscar logs relacionados (mesma inscrição ou mesmo payment_id)
    $logs_relacionados = [];
    
    if (!empty($log['inscricao_id'])) {
        $sql_rel = "
            SELECT id, nivel, acao, mensagem, created_at
            FROM logs_inscricoes_pagamentos
            WHERE inscricao_id = ? AND id != ?
            ORDER BY created_at ASC
        ";
        $stmt_rel = $pdo->prepare($sql_rel);
        $stmt_rel->execute([$log['inscricao_id'], $log_id]);
        $logs_relacionados = $stmt_rel->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($logs_relacionados as &$rel) {
            $rel['created_at_formatted'] = date('d/m/Y H:i:s', strtotime($rel['created_at']));
        }
    } elseif (!empty($log['payment_id'])) {
        $sql_rel = "
            SELECT id, nivel, acao, mensagem, created_at
            FROM logs_inscricoes_pagamentos
            WHERE payment_id = ? AND id != ?
            ORDER BY created_at ASC
        ";
        $stmt_rel = $pdo->prepare($sql_rel);
        $stmt_rel->execute([$log['payment_id'], $log_id]);
        $logs_relacionados = $stmt_rel->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($logs_relacionados as &$rel) {
            $rel['created_at_formatted'] = date('d/m/Y H:i:s', strtotime($rel['created_at']));
        }
    }
    
    $log['logs_relacionados'] = $logs_relacionados;
    
    echo json_encode([
        'success' => true,
        'log' => $log
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar detalhes: ' . $e->getMessage()
    ]);
    error_log('[LOGS_INSCRICOES] Erro ao buscar detalhes: ' . $e->getMessage());
}

