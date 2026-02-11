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
$ordem = $payload['ordem'] ?? null;

if (!is_array($ordem) || empty($ordem)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Lista de ordem inválida']);
    exit;
}

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("UPDATE banners SET ordem = :ordem WHERE id = :id");
    foreach ($ordem as $index => $id) {
        $stmt->execute([
            'ordem' => $index + 1,
            'id' => (int) $id
        ]);
    }
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Ordem atualizada']);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[ADMIN_BANNERS_REORDER] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao reordenar banners']);
}

