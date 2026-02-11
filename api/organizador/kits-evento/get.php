<?php
header('Content-Type: application/json');
require_once '../../db.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

try {
    $kit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $organizador_id = $_SESSION['user_id'];
    
    if ($kit_id <= 0) {
        throw new Exception('ID do kit é obrigatório');
    }
    
    // Buscar kit com validação de propriedade
    $sql = "
        SELECT 
            k.*,
            e.nome as evento_nome,
            e.id as evento_id
        FROM kits_eventos k
        INNER JOIN eventos e ON k.evento_id = e.id
        WHERE k.id = ? AND e.organizador_id = ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$kit_id, $organizador_id]);
    $kit = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$kit) {
        throw new Exception('Kit não encontrado ou não pertence a você');
    }
    
    // Buscar modalidades associadas
    $stmt = $pdo->prepare("SELECT modalidade_evento_id FROM kit_modalidade_evento WHERE kit_id = ?");
    $stmt->execute([$kit_id]);
    $kit['modalidades'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Buscar produtos do kit
    $stmt = $pdo->prepare("
        SELECT 
            kp.id,
            kp.produto_id,
            kp.tamanho_id,
            kp.quantidade,
            kp.ordem,
            p.nome as produto_nome,
            p.tipo as produto_tipo,
            p.descricao as produto_descricao,
            tce.tamanho,
            tce.quantidade_disponivel,
            tce.quantidade_vendida
        FROM kit_produtos kp
        INNER JOIN produtos p ON kp.produto_id = p.id
        LEFT JOIN tamanhos_camisetas_evento tce ON kp.tamanho_id = tce.id
        WHERE kp.kit_id = ? AND kp.ativo = 1
        ORDER BY kp.ordem ASC
    ");
    $stmt->execute([$kit_id]);
    $kit['produtos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar tamanhos disponíveis para o kit
    $stmt = $pdo->prepare("
        SELECT 
            kt.id,
            kt.tamanho_id,
            kt.quantidade_disponivel,
            kt.quantidade_vendida,
            tce.tamanho,
            tce.quantidade_inicial
        FROM kit_tamanhos kt
        INNER JOIN tamanhos_camisetas_evento tce ON kt.tamanho_id = tce.id
        WHERE kt.kit_id = ? AND kt.ativo = 1
        ORDER BY tce.tamanho ASC
    ");
    $stmt->execute([$kit_id]);
    $kit['tamanhos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular estoque total do kit
    $kit['estoque_total'] = 0;
    $kit['estoque_disponivel'] = 0;
    
    foreach ($kit['tamanhos'] as $tamanho) {
        $kit['estoque_total'] += $tamanho['quantidade_inicial'];
        $kit['estoque_disponivel'] += $tamanho['quantidade_disponivel'];
    }
    
    // Determinar status do kit
    if ($kit['ativo'] == 0) {
        $kit['status'] = 'inativo';
    } elseif ($kit['estoque_disponivel'] <= 0) {
        $kit['status'] = 'esgotado';
    } else {
        $kit['status'] = 'ativo';
    }
    
    // Calcular percentual de ocupação
    if ($kit['estoque_total'] > 0) {
        $kit['ocupacao_percentual'] = round((($kit['estoque_total'] - $kit['estoque_disponivel']) / $kit['estoque_total']) * 100, 1);
    } else {
        $kit['ocupacao_percentual'] = 0;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $kit
    ]);
    
} catch (Exception $e) {
    error_log('Erro ao buscar kit: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 
