<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth_middleware.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../helpers/email_helper.php';

header('Content-Type: application/json');

if (!requererAdmin(false)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true) ?? [];

$id = isset($payload['id']) ? (int) $payload['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, nome_completo, email FROM usuarios WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Organizador não encontrado']);
        exit;
    }

    $senhaTemporaria = bin2hex(random_bytes(4));
    $hash = password_hash($senhaTemporaria, PASSWORD_DEFAULT);

    $stmtUpdate = $pdo->prepare("UPDATE usuarios SET senha = :senha WHERE id = :id");
    $stmtUpdate->execute(['senha' => $hash, 'id' => $id]);

    $baseUrl = envValue('APP_URL', '');
    if ($baseUrl === '' && isset($_SERVER['HTTP_HOST'])) {
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
    }
    $loginUrl = rtrim($baseUrl, '/') . '/frontend/paginas/auth/login.php';

    $html = '
        <p>Olá ' . htmlspecialchars($usuario['nome_completo']) . ',</p>
        <p>Uma nova senha temporária foi gerada para sua conta no MovAmazon.</p>
        <ul>
            <li><strong>Login:</strong> ' . htmlspecialchars($usuario['email']) . '</li>
            <li><strong>Nova senha temporária:</strong> ' . $senhaTemporaria . '</li>
        </ul>
        <p>Faça login em: <a href="' . $loginUrl . '">' . $loginUrl . '</a> e altere a senha assim que possível.</p>
        <p>Equipe MovAmazon.</p>
    ';

    $emailOk = sendEmail($usuario['email'], 'Nova senha temporária - MovAmazon', $html);
    if (!$emailOk) {
        $mask = substr($senhaTemporaria, 0, 2) . str_repeat('*', max(0, strlen($senhaTemporaria) - 4)) . substr($senhaTemporaria, -2);
        error_log('[ADMIN_ORGANIZADORES_RESET_EMAIL_FAIL] id=' . $usuario['id'] . ' email=' . $usuario['email'] . ' temp_password=' . $mask);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Senha resetada e enviada por e-mail',
        'data' => [
            'senha_temporaria' => $senhaTemporaria
        ]
    ]);
} catch (Throwable $e) {
    error_log('[ADMIN_ORGANIZADORES_RESET_PASSWORD] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao resetar senha']);
}

