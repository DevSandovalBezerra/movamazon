<?php
// Desabilitar exibição de erros para não quebrar JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/auth_middleware.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');

if (!requererAdmin(false)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit();
}

try {
    $hoje = date('Y-m-d');
    $mes_atual = date('Y-m');
    $mes_anterior = date('Y-m', strtotime('-1 month'));
    
    // Total de Eventos
    $sql_eventos = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'ativo' THEN 1 ELSE 0 END) as ativos,
        SUM(CASE WHEN status = 'inativo' THEN 1 ELSE 0 END) as inativos,
        SUM(CASE WHEN COALESCE(data_realizacao, data_inicio) >= ? THEN 1 ELSE 0 END) as futuros
    FROM eventos 
    WHERE deleted_at IS NULL";
    $stmt_eventos = $pdo->prepare($sql_eventos);
    $stmt_eventos->execute([$hoje]);
    $eventos_stats = $stmt_eventos->fetch(PDO::FETCH_ASSOC);
    
    // Total de Inscrições
    $sql_inscricoes = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status_pagamento = 'pago' OR status = 'confirmada' THEN 1 ELSE 0 END) as confirmadas,
        SUM(CASE WHEN status_pagamento = 'pendente' THEN 1 ELSE 0 END) as pendentes
    FROM inscricoes";
    $stmt_inscricoes = $pdo->prepare($sql_inscricoes);
    $stmt_inscricoes->execute();
    $inscricoes_stats = $stmt_inscricoes->fetch(PDO::FETCH_ASSOC);
    
    // Pagamentos Pendentes (para widget)
    $pendentes_stats = ['total_pendentes' => 0, 'pendentes_24h' => 0, 'pendentes_72h' => 0];
    try {
        $sql_pendentes = "SELECT 
            COUNT(*) as total_pendentes,
            SUM(CASE WHEN TIMESTAMPDIFF(HOUR, data_inscricao, NOW()) >= 24 THEN 1 ELSE 0 END) as pendentes_24h,
            SUM(CASE WHEN TIMESTAMPDIFF(HOUR, data_inscricao, NOW()) >= 72 THEN 1 ELSE 0 END) as pendentes_72h
        FROM inscricoes
        WHERE status_pagamento = 'pendente'";
        $stmt_pendentes = $pdo->prepare($sql_pendentes);
        $stmt_pendentes->execute();
        $pendentes_stats = $stmt_pendentes->fetch(PDO::FETCH_ASSOC) ?: ['total_pendentes' => 0, 'pendentes_24h' => 0, 'pendentes_72h' => 0];
    } catch (Exception $e) {
        error_log('[ADMIN_DASHBOARD] Erro ao buscar pagamentos pendentes: ' . $e->getMessage());
        $pendentes_stats = ['total_pendentes' => 0, 'pendentes_24h' => 0, 'pendentes_72h' => 0];
    }
    
    // Solicitações de Cancelamento Pendentes (verificar se tabela existe)
    $cancelamentos_stats = ['total_pendentes' => 0];
    try {
        // Verificar se tabela existe
        $check_table = $pdo->query("
            SELECT COUNT(*) 
            FROM INFORMATION_SCHEMA.TABLES 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'solicitacoes_cancelamento'
        ")->fetchColumn();
        
        if ($check_table > 0) {
            $sql_cancelamentos = "SELECT 
                COUNT(*) as total_pendentes
            FROM solicitacoes_cancelamento
            WHERE status = 'pendente'";
            $stmt_cancelamentos = $pdo->prepare($sql_cancelamentos);
            $stmt_cancelamentos->execute();
            $cancelamentos_stats = $stmt_cancelamentos->fetch(PDO::FETCH_ASSOC) ?: ['total_pendentes' => 0];
        }
    } catch (Exception $e) {
        // Tabela não existe ou erro - usar valor padrão
        $cancelamentos_stats = ['total_pendentes' => 0];
        error_log('[ADMIN_DASHBOARD] Aviso: Tabela solicitacoes_cancelamento não encontrada ou erro: ' . $e->getMessage());
    }
    
    // Receita Total - Usar tabela pagamentos_ml padronizada
    $tabela_pagamentos = 'pagamentos_ml';
    $campo_valor = 'valor_pago';
    $campo_status = 'status';
    $campo_data = 'data_atualizacao';
    $campo_metodo = 'metodo_pagamento';
    $status_aprovado = 'pago';
    
    $sql_receita = "SELECT 
        COALESCE(SUM(CASE WHEN DATE_FORMAT($campo_data, '%Y-%m') = ? THEN $campo_valor ELSE 0 END), 0) as receita_mes_atual,
        COALESCE(SUM($campo_valor), 0) as receita_total
    FROM $tabela_pagamentos 
    WHERE $campo_status = ?";
    $stmt_receita = $pdo->prepare($sql_receita);
    $stmt_receita->execute([$mes_atual, $status_aprovado]);
    $receita_stats = $stmt_receita->fetch(PDO::FETCH_ASSOC);
    
    // Organizadores - Contar usuários com papel organizador ou que têm eventos
    $sql_organizadores = "SELECT 
        COUNT(DISTINCT u.id) as total,
        COUNT(DISTINCT CASE WHEN u.status = 'ativo' THEN u.id END) as ativos
    FROM usuarios u
    LEFT JOIN organizadores o ON u.id = o.usuario_id
    WHERE u.papel = 'organizador' OR o.id IS NOT NULL";
    $stmt_organizadores = $pdo->prepare($sql_organizadores);
    $stmt_organizadores->execute();
    $organizadores_stats = $stmt_organizadores->fetch(PDO::FETCH_ASSOC);
    
    // Participantes
    $sql_participantes = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'ativo' THEN 1 ELSE 0 END) as ativos
    FROM usuarios 
    WHERE papel = 'participante' OR papel IS NULL";
    $stmt_participantes = $pdo->prepare($sql_participantes);
    $stmt_participantes->execute();
    $participantes_stats = $stmt_participantes->fetch(PDO::FETCH_ASSOC);
    
    // Pagamentos
    $status_aprovado = 'pago';
    $status_pendente = 'pendente';
    
    $sql_pagamentos = "SELECT 
        SUM(CASE WHEN $campo_status = ? THEN 1 ELSE 0 END) as aprovados,
        SUM(CASE WHEN $campo_status = ? THEN 1 ELSE 0 END) as pendentes,
        SUM(CASE WHEN $campo_status = 'rejeitado' OR $campo_status = 'cancelado' THEN 1 ELSE 0 END) as rejeitados
    FROM $tabela_pagamentos";
    $stmt_pagamentos = $pdo->prepare($sql_pagamentos);
    $stmt_pagamentos->execute([$status_aprovado, $status_pendente]);
    $pagamentos_stats = $stmt_pagamentos->fetch(PDO::FETCH_ASSOC);
    
    // Eventos Recentes (últimos 5)
    $sql_eventos_recentes = "SELECT 
        e.id,
        e.nome,
        e.data_inicio,
        e.data_realizacao,
        e.status,
        e.imagem,
        COUNT(DISTINCT i.id) as total_inscricoes,
        COALESCE(SUM(CASE WHEN pm.$campo_status = ? THEN pm.$campo_valor ELSE 0 END), 0) as receita
    FROM eventos e
    LEFT JOIN inscricoes i ON i.evento_id = e.id
    LEFT JOIN $tabela_pagamentos pm ON pm.inscricao_id = i.id
    WHERE e.deleted_at IS NULL
    GROUP BY e.id
    ORDER BY e.data_criacao DESC
    LIMIT 5";
    $stmt_eventos_recentes = $pdo->prepare($sql_eventos_recentes);
    $stmt_eventos_recentes->execute([$status_aprovado]);
    $eventos_recentes = $stmt_eventos_recentes->fetchAll(PDO::FETCH_ASSOC);
    
    // Inscrições Recentes (últimas 10)
    $sql_inscricoes_recentes = "SELECT 
        i.id,
        i.numero_inscricao,
        i.status,
        i.status_pagamento,
        i.data_inscricao,
        u.nome_completo as participante_nome,
        e.nome as evento_nome,
        m.nome as modalidade_nome
    FROM inscricoes i
    JOIN usuarios u ON i.usuario_id = u.id
    JOIN eventos e ON i.evento_id = e.id
    JOIN modalidades m ON i.modalidade_evento_id = m.id
    ORDER BY i.data_inscricao DESC
    LIMIT 10";
    $stmt_inscricoes_recentes = $pdo->prepare($sql_inscricoes_recentes);
    $stmt_inscricoes_recentes->execute();
    $inscricoes_recentes = $stmt_inscricoes_recentes->fetchAll(PDO::FETCH_ASSOC);
    
    // Pagamentos Recentes (últimos 10)
    $campo_metodo_select = $campo_metodo ? "pm.$campo_metodo as payment_method_id" : "NULL as payment_method_id";
    $sql_pagamentos_recentes = "SELECT 
        pm.id,
        pm.$campo_status as status,
        pm.$campo_valor as transaction_amount,
        $campo_metodo_select,
        pm.$campo_data as data_atualizacao,
        i.numero_inscricao,
        u.nome_completo as participante_nome,
        e.nome as evento_nome
    FROM $tabela_pagamentos pm
    JOIN inscricoes i ON pm.inscricao_id = i.id
    JOIN usuarios u ON i.usuario_id = u.id
    JOIN eventos e ON i.evento_id = e.id
    ORDER BY pm.$campo_data DESC
    LIMIT 10";
    $stmt_pagamentos_recentes = $pdo->prepare($sql_pagamentos_recentes);
    $stmt_pagamentos_recentes->execute();
    $pagamentos_recentes = $stmt_pagamentos_recentes->fetchAll(PDO::FETCH_ASSOC);
    
    // Dados para gráficos - Receita Mensal (últimos 6 meses)
    $receita_mensal = [];
    for ($i = 5; $i >= 0; $i--) {
        $mes = date('Y-m', strtotime("-$i months"));
        $mes_nome = date('M/Y', strtotime("-$i months"));
        
        $sql_mes = "SELECT COALESCE(SUM($campo_valor), 0) as total
                    FROM $tabela_pagamentos 
                    WHERE $campo_status = ? 
                    AND DATE_FORMAT($campo_data, '%Y-%m') = ?";
        $stmt_mes = $pdo->prepare($sql_mes);
        $stmt_mes->execute([$status_aprovado, $mes]);
        $mes_data = $stmt_mes->fetch(PDO::FETCH_ASSOC);
        
        $receita_mensal[] = [
            'mes' => $mes_nome,
            'valor' => (float)$mes_data['total']
        ];
    }
    
    // Dados para gráficos - Inscrições por Mês (últimos 6 meses)
    $inscricoes_mensal = [];
    for ($i = 5; $i >= 0; $i--) {
        $mes = date('Y-m', strtotime("-$i months"));
        $mes_nome = date('M/Y', strtotime("-$i months"));
        
        $sql_mes = "SELECT COUNT(*) as total
                    FROM inscricoes 
                    WHERE DATE_FORMAT(data_inscricao, '%Y-%m') = ?";
        $stmt_mes = $pdo->prepare($sql_mes);
        $stmt_mes->execute([$mes]);
        $mes_data = $stmt_mes->fetch(PDO::FETCH_ASSOC);
        
        $inscricoes_mensal[] = [
            'mes' => $mes_nome,
            'total' => (int)$mes_data['total']
        ];
    }
    
    // Top 5 Eventos por Inscrições
    $sql_top_eventos = "SELECT 
        e.id,
        e.nome,
        COUNT(DISTINCT i.id) as total_inscricoes,
        COALESCE(SUM(CASE WHEN pm.$campo_status = ? THEN pm.$campo_valor ELSE 0 END), 0) as receita
    FROM eventos e
    LEFT JOIN inscricoes i ON i.evento_id = e.id
    LEFT JOIN $tabela_pagamentos pm ON pm.inscricao_id = i.id
    WHERE e.deleted_at IS NULL
    GROUP BY e.id
    ORDER BY total_inscricoes DESC
    LIMIT 5";
    $stmt_top_eventos = $pdo->prepare($sql_top_eventos);
    $stmt_top_eventos->execute([$status_aprovado]);
    $top_eventos = $stmt_top_eventos->fetchAll(PDO::FETCH_ASSOC);
    
    // Distribuição de Eventos por Status
    $sql_dist_eventos = "SELECT 
        status,
        COUNT(*) as total
    FROM eventos 
    WHERE deleted_at IS NULL
    GROUP BY status";
    $stmt_dist_eventos = $pdo->prepare($sql_dist_eventos);
    $stmt_dist_eventos->execute();
    $dist_eventos = $stmt_dist_eventos->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'eventos' => $eventos_stats,
            'inscricoes' => $inscricoes_stats,
            'receita' => $receita_stats,
        'organizadores' => $organizadores_stats,
        'participantes' => $participantes_stats,
        'pagamentos' => $pagamentos_stats,
        'pagamentos_pendentes' => $pendentes_stats ?? ['total_pendentes' => 0, 'pendentes_24h' => 0, 'pendentes_72h' => 0],
        'cancelamentos_pendentes' => $cancelamentos_stats ?? ['total_pendentes' => 0]
    ],
        'recentes' => [
            'eventos' => $eventos_recentes,
            'inscricoes' => $inscricoes_recentes,
            'pagamentos' => $pagamentos_recentes
        ],
        'graficos' => [
            'receita_mensal' => $receita_mensal,
            'inscricoes_mensal' => $inscricoes_mensal,
            'top_eventos' => $top_eventos,
            'dist_eventos' => $dist_eventos
        ]
    ]);
    
} catch (PDOException $e) {
    error_log('[ADMIN_DASHBOARD] Erro PDO: ' . $e->getMessage());
    error_log('[ADMIN_DASHBOARD] Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao buscar dados do dashboard',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('[ADMIN_DASHBOARD] Erro: ' . $e->getMessage());
    error_log('[ADMIN_DASHBOARD] Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao buscar dados do dashboard',
        'error' => $e->getMessage()
    ]);
}

