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
                m.nome as modalidade_nome,
                pe.nome_produto as produto_nome,
                pe.vagas_disponiveis as quantidade_inicial,
                pe.vagas_vendidas as quantidade_vendida,
                (pe.vagas_disponiveis - pe.vagas_vendidas) as estoque_atual,
                pe.valor as preco_unitario,
                (pe.vagas_vendidas * pe.valor) as receita_total
            FROM produtos_extras_modalidade pe
            INNER JOIN eventos e ON pe.evento_id = e.id
            INNER JOIN modalidades m ON pe.modalidade_evento_id = m.id
            WHERE e.organizador_id = ?
            ORDER BY e.data_inicio DESC, pe.nome_produto ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$organizador_id]);
    $estoque = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($estoque as &$item) {
        $item['data_evento_formatada'] = date('d/m/Y', strtotime($item['data_evento']));
        $item['preco_formatado'] = 'R$ ' . number_format($item['preco_unitario'], 2, ',', '.');
        $item['receita_formatada'] = 'R$ ' . number_format($item['receita_total'], 2, ',', '.');
        
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
        
        $item['percentual_vendido'] = $item['quantidade_inicial'] > 0 ? round(($item['quantidade_vendida'] / $item['quantidade_inicial']) * 100, 1) : 0;
    }
    
    echo json_encode(['success' => true, 'data' => $estoque]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?> 
