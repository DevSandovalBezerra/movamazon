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

$termoId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$termoId || $termoId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID do termo é obrigatório']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            t.id,
            t.titulo,
            t.conteudo,
            t.versao,
            t.ativo,
            t.data_criacao,
            COALESCE(t.tipo, 'inscricao') as tipo
        FROM termos_eventos t
        WHERE t.id = :id
    ");
    $stmt->execute(['id' => $termoId]);
    $termo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$termo) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Termo não encontrado']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'id' => (int)$termo['id'],
            'titulo' => $termo['titulo'],
            'conteudo' => $termo['conteudo'],
            'versao' => $termo['versao'],
            'ativo' => (bool)$termo['ativo'],
            'tipo' => $termo['tipo'] ?? 'inscricao',
            'data_criacao' => $termo['data_criacao']
        ]
    ]);
} catch (Throwable $e) {
    error_log('[ADMIN_TERMOS_GET] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar termo']);
}
