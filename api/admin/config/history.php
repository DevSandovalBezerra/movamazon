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

$key = isset($_GET['key']) ? trim($_GET['key']) : '';

if ($key === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parâmetro key é obrigatório']);
    exit;
}

$limit = isset($_GET['limit']) ? max(1, min(100, (int) $_GET['limit'])) : 50;

try {
    $stmt = $pdo->prepare('SELECT 
            ch.valor_antigo,
            ch.valor_novo,
            ch.alterado_por,
            ch.created_at,
            u.nome_completo AS alterado_por_nome
        FROM config_historico ch
        LEFT JOIN usuarios u ON u.id = ch.alterado_por
        WHERE ch.chave = :chave
        ORDER BY ch.created_at DESC
        LIMIT :limite');
    $stmt->bindValue(':chave', $key);
    $stmt->bindValue(':limite', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $historico
    ]);
} catch (Throwable $e) {
    error_log('[ADMIN_CONFIG_HISTORY] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao carregar histórico']);
}

