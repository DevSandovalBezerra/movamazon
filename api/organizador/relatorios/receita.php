<?php
header('Content-Type: application/json');
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

session_start();

if (!isset($_SESSION['papel']) && isset($_SESSION['user_role'])) {
    $_SESSION['papel'] = $_SESSION['user_role'];
}

if (!isset($_SESSION['user_id']) || ($_SESSION['papel'] ?? null) !== 'organizador') {
    http_response_code(401);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    $evento_id = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : null;
    
    $where_evento = $evento_id ? "AND e.id = ?" : "";
    $params = $evento_id ? [$organizador_id, $usuario_id, $evento_id] : [$organizador_id, $usuario_id];
    
    $sql = "SELECT 
                e.id as evento_id,
                e.nome as evento_nome,
                COALESCE(e.data_realizacao, e.data_inicio) as data_evento,
                me.nome as modalidade_nome,
                me.valor as valor_inscricao,
                COUNT(i.id) as inscricoes_confirmadas,
                (COUNT(i.id) * me.valor) as receita_inscricoes,
                COALESCE(SUM(pi.quantidade * pe.valor), 0) as receita_produtos,
                (COUNT(i.id) * me.valor) + COALESCE(SUM(pi.quantidade * pe.valor), 0) as receita_total
            FROM eventos e
            INNER JOIN modalidades_evento me ON e.id = me.evento_id
            LEFT JOIN inscricoes i ON me.id = i.modalidade_id AND i.status = 'confirmada'
            LEFT JOIN produtos_inscricao pi ON i.id = pi.inscricao_id AND pi.status = 'confirmada'
            LEFT JOIN produtos_extras_modalidade pe ON pi.produto_id = pe.id
            WHERE (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL $where_evento
            GROUP BY e.id, me.id
            ORDER BY COALESCE(e.data_realizacao, e.data_inicio) DESC, me.nome ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $relatorio = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $resumo = [
        'total_receita_inscricoes' => 0,
        'total_receita_produtos' => 0,
        'total_receita_geral' => 0,
        'total_inscricoes' => 0,
        'media_por_inscricao' => 0
    ];
    
    foreach ($relatorio as &$item) {
        $item['data_evento_formatada'] = date('d/m/Y', strtotime($item['data_evento']));
        $item['valor_inscricao_formatado'] = 'R$ ' . number_format($item['valor_inscricao'], 2, ',', '.');
        $item['receita_inscricoes_formatada'] = 'R$ ' . number_format($item['receita_inscricoes'], 2, ',', '.');
        $item['receita_produtos_formatada'] = 'R$ ' . number_format($item['receita_produtos'], 2, ',', '.');
        $item['receita_total_formatada'] = 'R$ ' . number_format($item['receita_total'], 2, ',', '.');
        
        $resumo['total_receita_inscricoes'] += $item['receita_inscricoes'];
        $resumo['total_receita_produtos'] += $item['receita_produtos'];
        $resumo['total_receita_geral'] += $item['receita_total'];
        $resumo['total_inscricoes'] += $item['inscricoes_confirmadas'];
    }
    
    $resumo['total_receita_inscricoes_formatado'] = 'R$ ' . number_format($resumo['total_receita_inscricoes'], 2, ',', '.');
    $resumo['total_receita_produtos_formatado'] = 'R$ ' . number_format($resumo['total_receita_produtos'], 2, ',', '.');
    $resumo['total_receita_geral_formatado'] = 'R$ ' . number_format($resumo['total_receita_geral'], 2, ',', '.');
    $resumo['media_por_inscricao'] = $resumo['total_inscricoes'] > 0 ? round($resumo['total_receita_geral'] / $resumo['total_inscricoes'], 2) : 0;
    $resumo['media_por_inscricao_formatado'] = 'R$ ' . number_format($resumo['media_por_inscricao'], 2, ',', '.');
    
    echo json_encode([
        'success' => true, 
        'data' => $relatorio,
        'resumo' => $resumo
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?> 
