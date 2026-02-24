<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../middleware.php';
requireAssessoriaAdminAPI();

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
    $assessor_id = isset($input['assessor_usuario_id']) ? (int) $input['assessor_usuario_id'] : null;

    if (!$vinculo_id) {
        throw new Exception('ID do vinculo e obrigatorio');
    }

    // Verificar vinculo
    $stmt = $pdo->prepare("SELECT id FROM assessoria_atletas WHERE id = ? AND assessoria_id = ? LIMIT 1");
    $stmt->execute([$vinculo_id, $assessoria_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Vinculo nao encontrado');
    }

    // Se atribuindo assessor, verificar se e da equipe
    if ($assessor_id) {
        $stmt = $pdo->prepare("
            SELECT id FROM assessoria_equipe 
            WHERE assessoria_id = ? AND usuario_id = ? AND status = 'ativo' 
            AND funcao IN ('admin', 'assessor')
            LIMIT 1
        ");
        $stmt->execute([$assessoria_id, $assessor_id]);
        if (!$stmt->fetch()) {
            throw new Exception('Assessor nao faz parte da equipe');
        }
    }

    $stmt = $pdo->prepare("UPDATE assessoria_atletas SET assessor_usuario_id = ? WHERE id = ?");
    $stmt->execute([$assessor_id, $vinculo_id]);

    echo json_encode(['success' => true, 'message' => 'Assessor atribuido com sucesso']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
