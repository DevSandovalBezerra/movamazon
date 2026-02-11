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
    $stmt = $pdo->prepare("SELECT status FROM solicitacoes_evento WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $solicitacao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$solicitacao) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Solicitação não encontrada']);
        exit;
    }

    if ($solicitacao['status'] !== 'recusado') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Apenas solicitações recusadas podem ser deletadas']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM solicitacoes_evento WHERE id = :id");
    $stmt->execute(['id' => $id]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Solicitação não encontrada']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Solicitação deletada com sucesso']);
} catch (Throwable $e) {
    error_log('[ADMIN_SOLICITACOES_DELETE] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao deletar solicitação']);
}

