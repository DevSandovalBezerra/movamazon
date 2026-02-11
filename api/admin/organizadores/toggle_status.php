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

$payload = json_decode(file_get_contents('php://input'), true) ?? [];

$id = isset($payload['id']) ? (int) $payload['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT status FROM usuarios WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Organizador não encontrado']);
        exit;
    }

    $novoStatus = $usuario['status'] === 'ativo' ? 'inativo' : 'ativo';

    $stmtUpdate = $pdo->prepare("UPDATE usuarios SET status = :status WHERE id = :id");
    $stmtUpdate->execute(['status' => $novoStatus, 'id' => $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Status atualizado com sucesso',
        'data' => ['status' => $novoStatus]
    ]);
} catch (Throwable $e) {
    error_log('[ADMIN_ORGANIZADORES_TOGGLE_STATUS] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status']);
}

