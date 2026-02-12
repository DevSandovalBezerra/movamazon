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

$titulo = isset($payload['titulo']) ? trim($payload['titulo']) : '';
$conteudo = isset($payload['conteudo']) ? trim($payload['conteudo']) : '';
$versao = isset($payload['versao']) ? trim($payload['versao']) : '1.0';
$tipo = isset($payload['tipo']) ? trim($payload['tipo']) : 'inscricao';
$ativo = isset($payload['ativo']) ? ($payload['ativo'] === true || $payload['ativo'] === '1' || $payload['ativo'] === 1) : true;

$tiposValidos = ['inscricao', 'anamnese', 'treino'];
if (!in_array($tipo, $tiposValidos)) {
    $tipo = 'inscricao';
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

    // Se está ativando um novo termo, desativar os outros do mesmo tipo (um ativo por tipo)
    if ($ativo) {
        $stmtDeactivate = $pdo->prepare("UPDATE termos_eventos SET ativo = 0 WHERE tipo = :tipo AND ativo = 1");
        $stmtDeactivate->execute(['tipo' => $tipo]);
    }

    // Inserir novo termo
    $stmt = $pdo->prepare("
        INSERT INTO termos_eventos 
        (titulo, conteudo, versao, tipo, ativo, data_criacao) 
        VALUES (:titulo, :conteudo, :versao, :tipo, :ativo, NOW())
    ");

    $stmt->execute([
        'titulo' => $titulo,
        'conteudo' => $conteudo,
        'versao' => $versao,
        'tipo' => $tipo,
        'ativo' => $ativo ? 1 : 0
    ]);

    $termoId = $pdo->lastInsertId();

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Termo criado com sucesso',
        'data' => [
            'id' => (int)$termoId,
            'titulo' => $titulo,
            'versao' => $versao,
            'tipo' => $tipo,
            'ativo' => $ativo
        ]
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[ADMIN_TERMOS_CREATE] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao criar termo']);
}
