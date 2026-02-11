<?php
header('Content-Type: application/json');
require_once '../../db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $organizadorId = $_SESSION['user_id'];
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['id'])) {
        throw new Exception('ID do termo é obrigatório');
    }

    $termoId = (int)$input['id'];

    if ($termoId <= 0) {
        throw new Exception('ID do termo inválido');
    }

    // Verificar se o termo existe e pertence ao organizador
    $stmt = $pdo->prepare("
        SELECT t.id 
        FROM termos_eventos t
        INNER JOIN eventos e ON t.evento_id = e.id
        WHERE t.id = ? AND e.organizador_id = ?
    ");
    $stmt->execute([$termoId, $organizadorId]);
    if (!$stmt->fetch()) {
        throw new Exception('Termo não encontrado ou sem permissão');
    }

    // Excluir termo
    $stmt = $pdo->prepare("DELETE FROM termos_eventos WHERE id = ?");
    $stmt->execute([$termoId]);

    echo json_encode([
        'success' => true,
        'message' => 'Termo excluído com sucesso'
    ]);
} catch (Exception $e) {
    error_log('Erro ao excluir termo: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
