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

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$termoId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$termoId || $termoId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID do termo é obrigatório']);
    exit;
}

try {
    // Verificar se o termo existe
    $stmtCheck = $pdo->prepare("SELECT id, ativo FROM termos_eventos WHERE id = :id LIMIT 1");
    $stmtCheck->execute(['id' => $termoId]);
    $termo = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$termo) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Termo não encontrado']);
        exit;
    }

    // Verificar se há aceites vinculados a este termo
    $stmtAceites = $pdo->prepare("SELECT COUNT(*) as total FROM aceites_termos WHERE termos_id = :termo_id");
    $stmtAceites->execute(['termo_id' => $termoId]);
    $totalAceites = (int)$stmtAceites->fetch(PDO::FETCH_ASSOC)['total'];

    // Se o termo está ativo e há aceites, não permitir exclusão
    if ($termo['ativo'] == 1 && $totalAceites > 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Não é possível excluir um termo ativo que possui aceites. Desative o termo primeiro.'
        ]);
        exit;
    }

    // Deletar o termo
    $stmt = $pdo->prepare("DELETE FROM termos_eventos WHERE id = :id");
    $stmt->execute(['id' => $termoId]);

    echo json_encode([
        'success' => true,
        'message' => 'Termo excluído com sucesso'
    ]);
} catch (Throwable $e) {
    error_log('[ADMIN_TERMOS_DELETE] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir termo']);
}
