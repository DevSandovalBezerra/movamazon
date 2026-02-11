<?php
header('Content-Type: application/json');
require_once '../../db.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

try {
    $organizador_id = $_SESSION['user_id'];
    
                $sql = "SELECT 
                e.nome as evento_nome,
                e.data_inicio as data_evento,
                'N/A' as modalidade_nome,
                c.tamanho,
                c.quantidade_inicial,
                c.quantidade_vendida as quantidade_retirada,
                c.quantidade_disponivel as estoque_atual,
                ROUND((c.quantidade_vendida / c.quantidade_inicial) * 100, 1) as percentual_utilizado
            FROM camisas c
            INNER JOIN eventos e ON c.evento_id = e.id
            WHERE e.organizador_id = ?
            ORDER BY e.data_inicio DESC, c.tamanho ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$organizador_id]);
    $estoque = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($estoque as &$item) {
        $item['data_evento_formatada'] = date('d/m/Y', strtotime($item['data_evento']));
        
        if ($item['estoque_atual'] <= 0) {
            $item['status_class'] = 'danger';
            $item['status'] = 'esgotado';
        } elseif ($item['estoque_atual'] <= 5) {
            $item['status_class'] = 'warning';
            $item['status'] = 'estoque_baixo';
        } else {
            $item['status_class'] = 'success';
            $item['status'] = 'disponivel';
        }
    }
    
    echo json_encode(['success' => true, 'data' => $estoque]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?> 
