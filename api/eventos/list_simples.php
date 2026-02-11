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

    $sql = "SELECT id, nome FROM eventos WHERE organizador_id = ? ORDER BY nome ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$organizadorId]);
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'eventos' => $eventos
    ]);
} catch (Exception $e) {
    error_log('Erro ao listar eventos simples: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
