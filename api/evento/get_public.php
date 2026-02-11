<?php
require_once '../../db.php';
header('Content-Type: application/json');

$evento_id = $_GET['id'] ?? 0;
    if (!$evento_id) {
        http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID do evento não fornecido.']);
        exit();
    }
    
try {
    $response = ['success' => true];

    // 1. DADOS PRINCIPAIS DO EVENTO
    $stmt = $pdo->prepare("SELECT nome, descricao, COALESCE(data_realizacao, data_inicio) AS data_evento, hora_inicio, local, cidade, estado, imagem, regulamento FROM eventos WHERE id = ? AND status = 'ativo'");
    $stmt->execute([$evento_id]);
    $response['evento'] = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$response['evento']) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Evento não encontrado ou inativo.']);
        exit();
    }
    
    // 2. MODALIDADES E LOTES ATIVOS
    // Esta query complexa busca as modalidades e, para cada uma, o lote ativo correspondente (se houver).
    $sql_modalidades = "
        SELECT 
            m.id, m.nome, m.distancia, m.descricao,
            li.preco, li.taxa_servico
        FROM modalidades m
        LEFT JOIN lotes_inscricao li ON m.id = li.modalidade_id
        WHERE m.evento_id = ?
          AND m.ativo = 1
          AND li.ativo = 1
          AND CURDATE() BETWEEN li.data_inicio AND li.data_fim
        ORDER BY m.distancia, m.nome
    ";
    $stmt = $pdo->prepare($sql_modalidades);
    $stmt->execute([$evento_id]);
    $response['modalidades'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 3. PROGRAMAÇÃO DO EVENTO
    $stmt = $pdo->prepare("SELECT tipo, titulo, descricao FROM programacao_evento WHERE evento_id = ? AND ativo = 1 ORDER BY ordem");
    $stmt->execute([$evento_id]);
    $response['programacao'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. INFORMAÇÕES DE RETIRADA DE KITS
    $stmt = $pdo->prepare("SELECT * FROM retirada_kits_evento WHERE evento_id = ? AND ativo = 1");
    $stmt->execute([$evento_id]);
    $response['retirada_kit'] = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}

