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
    $stats = [];
    
    // Estatísticas gerais por nível
    $sql_nivel = "SELECT 
        nivel,
        COUNT(*) as total
    FROM logs_inscricoes_pagamentos
    GROUP BY nivel";
    $stmt_nivel = $pdo->query($sql_nivel);
    $stats['por_nivel'] = [];
    while ($row = $stmt_nivel->fetch(PDO::FETCH_ASSOC)) {
        $stats['por_nivel'][$row['nivel']] = (int)$row['total'];
    }
    
    // Estatísticas por ação (top 10 com mais erros)
    $sql_acao = "SELECT 
        acao,
        nivel,
        COUNT(*) as total
    FROM logs_inscricoes_pagamentos
    WHERE nivel IN ('ERROR', 'WARNING')
    GROUP BY acao, nivel
    ORDER BY total DESC
    LIMIT 10";
    $stmt_acao = $pdo->query($sql_acao);
    $stats['top_acoes_erros'] = $stmt_acao->fetchAll(PDO::FETCH_ASSOC);
    
    // Estatísticas por período
    $periodos = [
        'ultimas_24h' => date('Y-m-d H:i:s', strtotime('-24 hours')),
        'ultimos_7_dias' => date('Y-m-d H:i:s', strtotime('-7 days')),
        'ultimos_30_dias' => date('Y-m-d H:i:s', strtotime('-30 days'))
    ];
    
    $stats['por_periodo'] = [];
    foreach ($periodos as $periodo => $data_limite) {
        $sql_periodo = "SELECT 
            nivel,
            COUNT(*) as total
        FROM logs_inscricoes_pagamentos
        WHERE created_at >= ?
        GROUP BY nivel";
        $stmt_periodo = $pdo->prepare($sql_periodo);
        $stmt_periodo->execute([$data_limite]);
        $stats['por_periodo'][$periodo] = [];
        while ($row = $stmt_periodo->fetch(PDO::FETCH_ASSOC)) {
            $stats['por_periodo'][$periodo][$row['nivel']] = (int)$row['total'];
        }
    }
    
    // Gráfico de problemas ao longo do tempo (últimos 30 dias, agrupado por dia)
    $sql_grafico = "SELECT 
        DATE(created_at) as data,
        nivel,
        COUNT(*) as total
    FROM logs_inscricoes_pagamentos
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at), nivel
    ORDER BY data ASC";
    $stmt_grafico = $pdo->query($sql_grafico);
    $grafico_raw = $stmt_grafico->fetchAll(PDO::FETCH_ASSOC);
    
    $stats['grafico_tempo'] = [];
    foreach ($grafico_raw as $row) {
        $data = $row['data'];
        if (!isset($stats['grafico_tempo'][$data])) {
            $stats['grafico_tempo'][$data] = [
                'data' => $data,
                'ERROR' => 0,
                'WARNING' => 0,
                'INFO' => 0,
                'SUCCESS' => 0
            ];
        }
        $stats['grafico_tempo'][$data][$row['nivel']] = (int)$row['total'];
    }
    $stats['grafico_tempo'] = array_values($stats['grafico_tempo']);
    
    // Total geral
    $sql_total = "SELECT COUNT(*) as total FROM logs_inscricoes_pagamentos";
    $stmt_total = $pdo->query($sql_total);
    $stats['total_geral'] = (int)$stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar estatísticas: ' . $e->getMessage()
    ]);
    error_log('[LOGS_INSCRICOES] Erro ao buscar stats: ' . $e->getMessage());
}

