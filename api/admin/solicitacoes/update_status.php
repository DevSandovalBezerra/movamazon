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
$status = trim($payload['status'] ?? '');

$allowedStatus = ['novo', 'em_analise', 'aprovado', 'recusado'];

if ($id <= 0 || !in_array($status, $allowedStatus, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE solicitacoes_evento SET status = :status WHERE id = :id");
    $stmt->execute(['status' => $status, 'id' => $id]);

    echo json_encode(['success' => true, 'message' => 'Status atualizado']);
} catch (Throwable $e) {
    error_log('[ADMIN_SOLICITACOES_STATUS] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status']);
}

