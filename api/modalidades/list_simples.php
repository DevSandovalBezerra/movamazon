<?php
header('Content-Type: application/json');
require_once '../../db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

try {
    $organizadorId = $_SESSION['user_id'];
    $eventoId = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : null;

    if (!$eventoId) {
        echo json_encode(['success' => false, 'message' => 'ID do evento é obrigatório']);
        exit;
    }

    // Verificar se o evento pertence ao organizador
    $stmt = $pdo->prepare("SELECT id FROM eventos WHERE id = ? AND organizador_id = ?");
    $stmt->execute([$eventoId, $organizadorId]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Evento não encontrado ou sem permissão']);
        exit;
    }

    $sql = "SELECT id, nome FROM modalidades WHERE evento_id = ? ORDER BY nome ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$eventoId]);
    $modalidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'modalidades' => $modalidades
    ]);
} catch (Exception $e) {
    error_log('Erro ao listar modalidades simples: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
