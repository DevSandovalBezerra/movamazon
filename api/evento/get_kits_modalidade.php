<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db.php';

$modalidade_evento_id = $_GET['modalidade_evento_id'] ?? null;

if (!$modalidade_evento_id) {
    echo json_encode(['success' => false, 'message' => 'ID da modalidade é obrigatório']);
    exit;
}

try {
    // Buscar kits da modalidade
    $stmt = $pdo->prepare('
        SELECT 
            ke.id,
            ke.nome as nome_kit,
            ke.descricao as descricao_kit,
            ke.valor,
            m.nome as nome_modalidade,
            c.nome as nome_categoria
        FROM kits_eventos ke
        INNER JOIN modalidades m ON ke.modalidade_evento_id = m.id
        INNER JOIN categorias c ON m.categoria_id = c.id
        WHERE ke.modalidade_evento_id = ? AND ke.ativo = 1
        ORDER BY ke.valor ASC
    ');
    $stmt->execute([$modalidade_evento_id]);
    $kits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Para cada kit, buscar seus itens
    foreach ($kits as &$kit) {
        $stmt = $pdo->prepare('
            SELECT item, descricao, quantidade, ordem
            FROM itens_kits_modalidades
            WHERE kit_modalidade_id = ? AND ativo = 1
            ORDER BY ordem ASC
        ');
        $stmt->execute([$kit['id']]);
        $kit['itens'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    $response = [
        'success' => true,
        'data' => [
            'modalidade' => [
                'nome' => $kits[0]['nome_modalidade'] ?? '',
                'categoria' => $kits[0]['nome_categoria'] ?? ''
            ],
            'kits' => $kits
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log('Erro ao buscar kits da modalidade: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao carregar kits']);
}
?> 
