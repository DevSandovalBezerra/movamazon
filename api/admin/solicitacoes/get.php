<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth_middleware.php';
require_once __DIR__ . '/../../db.php';

header('Content-Type: application/json');

if (!requererAdmin(false)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM solicitacoes_evento WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $solicitacao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$solicitacao) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Solicitação não encontrada']);
        exit;
    }

    echo json_encode(['success' => true, 'data' => $solicitacao]);
} catch (Throwable $e) {
    error_log('[ADMIN_SOLICITACOES_GET] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao carregar solicitação']);
}

