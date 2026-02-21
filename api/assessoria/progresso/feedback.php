<?php
/**
 * Assessor adiciona feedback a um registro de progresso
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../assessoria/middleware.php';
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
    $progresso_id = (int) ($input['progresso_id'] ?? 0);
    $feedback = trim($input['feedback'] ?? '');

    if (!$progresso_id) {
        throw new Exception('ID do progresso e obrigatorio');
    }
    if (!$feedback) {
        throw new Exception('Feedback e obrigatorio');
    }

    // Verificar se o progresso pertence a um atleta da assessoria
    $stmt = $pdo->prepare("
        SELECT pt.id FROM progresso_treino pt
        JOIN assessoria_atletas aa ON pt.usuario_id = aa.atleta_usuario_id
        WHERE pt.id = ? AND aa.assessoria_id = ? AND aa.status = 'ativo'
        LIMIT 1
    ");
    $stmt->execute([$progresso_id, $assessoria_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Registro de progresso nao encontrado');
    }

    $stmt = $pdo->prepare("UPDATE progresso_treino SET feedback_assessor = ? WHERE id = ?");
    $stmt->execute([$feedback, $progresso_id]);

    echo json_encode(['success' => true, 'message' => 'Feedback registrado']);
} catch (Exception $e) {
    error_log("[PROGRESSO_FEEDBACK] Erro: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
