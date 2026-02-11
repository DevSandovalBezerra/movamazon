<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth_middleware.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../helpers/config_helper.php';

header('Content-Type: application/json');

if (!requererAdmin(false)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);

if (!is_array($payload) || empty($payload['key']) || !array_key_exists('value', $payload)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

$key = trim($payload['key']);
$value = $payload['value'];

try {
    $stmt = $pdo->prepare('SELECT id, tipo, editavel FROM config WHERE chave = :chave LIMIT 1');
    $stmt->execute(['chave' => $key]);
    $config = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$config) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Configuração não encontrada']);
        exit;
    }

    if (!(bool) $config['editavel']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Configuração não é editável']);
        exit;
    }

    $userId = $_SESSION['user_id'] ?? null;
    ConfigHelper::set($key, $value, $userId);

    echo json_encode([
        'success' => true,
        'message' => 'Configuração atualizada com sucesso',
        'data' => [
            'key' => $key,
            'valor' => ConfigHelper::get($key),
            'tipo' => $config['tipo']
        ]
    ]);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log('[ADMIN_CONFIG_UPDATE] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar configuração']);
}

