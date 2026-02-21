<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../assessoria/middleware.php';
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
    $programa_id = (int) ($input['programa_id'] ?? 0);
    $atleta_usuario_id = (int) ($input['atleta_usuario_id'] ?? 0);

    if (!$programa_id || !$atleta_usuario_id) {
        throw new Exception('Dados incompletos');
    }

    // Verificar propriedade do programa
    $stmt = $pdo->prepare("SELECT id FROM assessoria_programas WHERE id = ? AND assessoria_id = ? LIMIT 1");
    $stmt->execute([$programa_id, $assessoria_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Programa nao encontrado');
    }

    $stmt = $pdo->prepare("
        UPDATE assessoria_programa_atletas SET status = 'encerrado' 
        WHERE programa_id = ? AND atleta_usuario_id = ?
    ");
    $stmt->execute([$programa_id, $atleta_usuario_id]);

    echo json_encode(['success' => true, 'message' => 'Atleta removido do programa']);
} catch (Exception $e) {
    error_log("[PROG_ATLETAS_REMOVE] Erro: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
