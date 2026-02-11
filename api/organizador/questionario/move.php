<?php
header('Content-Type: application/json');
require_once '../../db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticação
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id = $input['id'] ?? null;
    $direcao = $input['direcao'] ?? null; // 'up' ou 'down'
    
    if (!$id || !in_array($direcao, ['up', 'down'])) {
        echo json_encode(['success' => false, 'message' => 'Parâmetros obrigatórios: id, direcao (up/down)']);
        exit;
    }

    // Verificar se a pergunta existe e pertence ao organizador
    $stmt = $pdo->prepare("
        SELECT qe.id, qe.evento_id, qe.ordem 
        FROM questionario_evento qe 
        INNER JOIN eventos e ON qe.evento_id = e.id 
        WHERE qe.id = ? AND e.organizador_id = ?
    ");
    $stmt->execute([$id, $_SESSION['user_id']]);
    $pergunta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pergunta) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Pergunta não encontrada ou acesso negado']);
        exit;
    }

    $ordem_atual = $pergunta['ordem'];
    $evento_id = $pergunta['evento_id'];
    
    // Determinar nova ordem
    $nova_ordem = ($direcao === 'up') ? $ordem_atual - 1 : $ordem_atual + 1;
    
    // Verificar se a nova ordem é válida
    if ($nova_ordem < 1) {
        echo json_encode(['success' => false, 'message' => 'A pergunta já está na primeira posição']);
        exit;
    }

    // Verificar se existe uma pergunta na nova ordem
    $stmt = $pdo->prepare("SELECT id FROM questionario_evento WHERE evento_id = ? AND ordem = ?");
    $stmt->execute([$evento_id, $nova_ordem]);
    $pergunta_destino = $stmt->fetch();
    
    if (!$pergunta_destino) {
        echo json_encode(['success' => false, 'message' => 'Não é possível mover para esta posição']);
        exit;
    }

    $pdo->beginTransaction();

    // Trocar as ordens
    // 1. Temporariamente definir ordem como 0 para evitar conflito de unique
    $stmt = $pdo->prepare("UPDATE questionario_evento SET ordem = 0 WHERE id = ?");
    $stmt->execute([$id]);
    
    // 2. Mover a pergunta destino para a ordem atual
    $stmt = $pdo->prepare("UPDATE questionario_evento SET ordem = ? WHERE id = ?");
    $stmt->execute([$ordem_atual, $pergunta_destino['id']]);
    
    // 3. Mover a pergunta original para a nova ordem
    $stmt = $pdo->prepare("UPDATE questionario_evento SET ordem = ? WHERE id = ?");
    $stmt->execute([$nova_ordem, $id]);

    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Ordem alterada com sucesso'
    ]);

} catch (Exception $e) {
    $pdo->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 
