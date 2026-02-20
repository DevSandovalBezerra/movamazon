<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/middleware.php';
requireAssessorAPI();

$assessoria_id = getAssessoriaDoUsuario();

if (!$assessoria_id) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Assessoria nao encontrada para este usuario']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM assessorias WHERE id = ? LIMIT 1");
    $stmt->execute([$assessoria_id]);
    $assessoria = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$assessoria) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Assessoria nao encontrada']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'assessoria' => $assessoria
    ]);
} catch (Exception $e) {
    error_log("[ASSESSORIA_ME] Erro: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno']);
}
