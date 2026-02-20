<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../middleware.php';
requireAssessorAPI();

$assessoria_id = getAssessoriaDoUsuario();
if (!$assessoria_id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Assessoria nao encontrada']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo nao permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $vinculo_id = (int) ($input['vinculo_id'] ?? 0);
    $novo_status = $input['status'] ?? '';

    if (!$vinculo_id || !in_array($novo_status, ['ativo', 'pausado', 'encerrado'])) {
        throw new Exception('Dados invalidos');
    }

    $stmt = $pdo->prepare("
        SELECT id FROM assessoria_atletas WHERE id = ? AND assessoria_id = ? LIMIT 1
    ");
    $stmt->execute([$vinculo_id, $assessoria_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Vinculo nao encontrado');
    }

    $stmt = $pdo->prepare("UPDATE assessoria_atletas SET status = ? WHERE id = ?");
    $stmt->execute([$novo_status, $vinculo_id]);

    echo json_encode(['success' => true, 'message' => 'Status atualizado']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
