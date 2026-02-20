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
    $convite_id = (int) ($input['convite_id'] ?? 0);

    if (!$convite_id) {
        throw new Exception('ID do convite e obrigatorio');
    }

    $stmt = $pdo->prepare("
        SELECT id, status FROM assessoria_convites 
        WHERE id = ? AND assessoria_id = ? LIMIT 1
    ");
    $stmt->execute([$convite_id, $assessoria_id]);
    $convite = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$convite) {
        throw new Exception('Convite nao encontrado');
    }
    if ($convite['status'] !== 'pendente') {
        throw new Exception('Apenas convites pendentes podem ser cancelados');
    }

    $stmt = $pdo->prepare("UPDATE assessoria_convites SET status = 'cancelado', respondido_em = NOW() WHERE id = ?");
    $stmt->execute([$convite_id]);

    echo json_encode(['success' => true, 'message' => 'Convite cancelado']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
