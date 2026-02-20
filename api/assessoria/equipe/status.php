<?php
session_start();
header('Content-Type: application/json');

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
    $membro_id = (int) ($input['membro_id'] ?? 0);
    $novo_status = $input['status'] ?? '';

    if (!$membro_id || !in_array($novo_status, ['ativo', 'inativo'])) {
        throw new Exception('Dados invalidos');
    }

    // Verificar se o membro pertence a esta assessoria e nao e admin
    $stmt = $pdo->prepare("
        SELECT id, funcao FROM assessoria_equipe 
        WHERE id = ? AND assessoria_id = ? LIMIT 1
    ");
    $stmt->execute([$membro_id, $assessoria_id]);
    $membro = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$membro) {
        throw new Exception('Membro nao encontrado nesta assessoria');
    }
    if ($membro['funcao'] === 'admin') {
        throw new Exception('Nao e possivel alterar o status do administrador');
    }

    $stmt = $pdo->prepare("UPDATE assessoria_equipe SET status = ? WHERE id = ?");
    $stmt->execute([$novo_status, $membro_id]);

    error_log("[ASSESSORIA_EQUIPE_STATUS] Membro {$membro_id} alterado para {$novo_status} na assessoria {$assessoria_id}");

    echo json_encode([
        'success' => true,
        'message' => 'Status atualizado com sucesso'
    ]);
} catch (Exception $e) {
    error_log("[ASSESSORIA_EQUIPE_STATUS] Erro: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
