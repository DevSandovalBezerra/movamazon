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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true) ?? [];

$organizadorId = isset($payload['organizador_id']) ? (int)$payload['organizador_id'] : 0;
$titulo = isset($payload['titulo']) ? trim($payload['titulo']) : '';
$conteudo = isset($payload['conteudo']) ? trim($payload['conteudo']) : '';
$versao = isset($payload['versao']) ? trim($payload['versao']) : '1.0';
$ativo = isset($payload['ativo']) ? ($payload['ativo'] === true || $payload['ativo'] === '1' || $payload['ativo'] === 1) : true;

if ($organizadorId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Organizador é obrigatório']);
    exit;
}

if (empty($titulo)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Título é obrigatório']);
    exit;
}

if (empty($conteudo)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Conteúdo é obrigatório']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Verificar se o organizador existe
    $stmtCheck = $pdo->prepare("SELECT id FROM organizadores WHERE id = :id LIMIT 1");
    $stmtCheck->execute(['id' => $organizadorId]);
    if (!$stmtCheck->fetch()) {
        throw new RuntimeException('Organizador não encontrado');
    }

    // Se está ativando um novo termo, desativar o termo ativo anterior do organizador
    if ($ativo) {
        $stmtDeactivate = $pdo->prepare("UPDATE termos_eventos SET ativo = 0 WHERE organizador_id = :organizador_id AND ativo = 1");
        $stmtDeactivate->execute(['organizador_id' => $organizadorId]);
    }

    // Inserir novo termo
    $stmt = $pdo->prepare("
        INSERT INTO termos_eventos 
        (organizador_id, titulo, conteudo, versao, ativo, data_criacao) 
        VALUES (:organizador_id, :titulo, :conteudo, :versao, :ativo, NOW())
    ");

    $stmt->execute([
        'organizador_id' => $organizadorId,
        'titulo' => $titulo,
        'conteudo' => $conteudo,
        'versao' => $versao,
        'ativo' => $ativo ? 1 : 0
    ]);

    $termoId = $pdo->lastInsertId();

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Termo criado com sucesso',
        'data' => [
            'id' => (int)$termoId,
            'organizador_id' => $organizadorId,
            'titulo' => $titulo,
            'versao' => $versao,
            'ativo' => $ativo
        ]
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[ADMIN_TERMOS_CREATE] ' . $e->getMessage());
    http_response_code(500);
    $message = $e->getMessage() === 'Organizador não encontrado' ? 'Organizador não encontrado' : 'Erro ao criar termo';
    echo json_encode(['success' => false, 'message' => $message]);
}

