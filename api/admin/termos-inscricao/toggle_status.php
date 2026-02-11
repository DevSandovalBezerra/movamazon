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

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PATCH') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true) ?? [];

$termoId = isset($payload['id']) ? (int)$payload['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : null);
$novoStatus = isset($payload['ativo']) ? ($payload['ativo'] === true || $payload['ativo'] === '1' || $payload['ativo'] === 1) : null;

if (!$termoId || $termoId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID do termo é obrigatório']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Verificar se o termo existe e obter organizador_id
    $stmtCheck = $pdo->prepare("SELECT id, organizador_id, ativo FROM termos_eventos WHERE id = :id LIMIT 1");
    $stmtCheck->execute(['id' => $termoId]);
    $termo = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$termo) {
        throw new RuntimeException('Termo não encontrado');
    }

    $organizadorId = (int)$termo['organizador_id'];
    $statusAtual = (bool)$termo['ativo'];

    // Se não especificou novo status, alternar
    if ($novoStatus === null) {
        $novoStatus = !$statusAtual;
    }

    // Se está ativando, desativar outros termos ativos do mesmo organizador
    if ($novoStatus && !$statusAtual) {
        $stmtDeactivate = $pdo->prepare("UPDATE termos_eventos SET ativo = 0 WHERE organizador_id = :organizador_id AND id != :termo_id AND ativo = 1");
        $stmtDeactivate->execute([
            'organizador_id' => $organizadorId,
            'termo_id' => $termoId
        ]);
    }

    // Atualizar status do termo
    $stmt = $pdo->prepare("UPDATE termos_eventos SET ativo = :ativo WHERE id = :id");
    $stmt->execute([
        'ativo' => $novoStatus ? 1 : 0,
        'id' => $termoId
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => $novoStatus ? 'Termo ativado com sucesso' : 'Termo desativado com sucesso',
        'data' => [
            'id' => $termoId,
            'ativo' => $novoStatus
        ]
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[ADMIN_TERMOS_TOGGLE] ' . $e->getMessage());
    http_response_code(500);
    $message = $e->getMessage() === 'Termo não encontrado' ? 'Termo não encontrado' : 'Erro ao alterar status do termo';
    echo json_encode(['success' => false, 'message' => $message]);
}

