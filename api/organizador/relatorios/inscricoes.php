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
                me.valor,
                COUNT(i.id) as total_inscritos,
                SUM(CASE WHEN i.status = 'confirmada' THEN 1 ELSE 0 END) as inscritos_confirmados,
                SUM(CASE WHEN i.status = 'pendente' THEN 1 ELSE 0 END) as inscritos_pendentes,
                SUM(CASE WHEN i.status = 'cancelada' THEN 1 ELSE 0 END) as inscritos_cancelados,
                (COUNT(i.id) * me.valor) as receita_total
            FROM eventos e
            INNER JOIN modalidades_evento me ON e.id = me.evento_id
            LEFT JOIN inscricoes i ON me.id = i.modalidade_id
            WHERE (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL $where_evento
            GROUP BY e.id, me.id
            ORDER BY COALESCE(e.data_realizacao, e.data_inicio) DESC, me.nome ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $relatorio = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $resumo = [
        'total_eventos' => 0,
        'total_inscritos' => 0,
        'total_receita' => 0,
        'media_inscritos_por_evento' => 0
    ];
    
    foreach ($relatorio as &$item) {
        $item['data_evento_formatada'] = date('d/m/Y', strtotime($item['data_evento']));
        $item['valor_formatado'] = 'R$ ' . number_format($item['valor'], 2, ',', '.');
        $item['receita_formatada'] = 'R$ ' . number_format($item['receita_total'], 2, ',', '.');
        $item['taxa_confirmacao'] = $item['total_inscritos'] > 0 ? round(($item['inscritos_confirmados'] / $item['total_inscritos']) * 100, 1) : 0;
        
        $resumo['total_inscritos'] += $item['total_inscritos'];
        $resumo['total_receita'] += $item['receita_total'];
    }
    
    $resumo['total_eventos'] = count(array_unique(array_column($relatorio, 'evento_id')));
    $resumo['total_receita_formatado'] = 'R$ ' . number_format($resumo['total_receita'], 2, ',', '.');
    $resumo['media_inscritos_por_evento'] = $resumo['total_eventos'] > 0 ? round($resumo['total_inscritos'] / $resumo['total_eventos'], 1) : 0;
    
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
