<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';

$response = ['success' => false, 'message' => ''];

$input = json_decode(file_get_contents('php://input'), true);
$token = trim($input['token'] ?? '');
$senha = trim($input['senha'] ?? '');

// Log recebimento dos dados
error_log("[resetar_senha] Requisição recebida. Token: " . substr($token, 0, 8) . "...");

if (empty($token) || empty($senha)) {
    error_log("[resetar_senha] Token ou senha vazios.");
    echo json_encode(['success' => false, 'message' => 'Token e nova senha são obrigatórios.']);
    exit;
}

if (strlen($senha) < 6) {
    error_log("[resetar_senha] Senha muito curta.");
    echo json_encode(['success' => false, 'message' => 'A senha deve ter pelo menos 6 caracteres.']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id, token_expira FROM usuarios WHERE token_recuperacao = ?');
    $stmt->execute([$token]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        error_log("[resetar_senha] Token inválido: " . substr($token, 0, 8) . "...");
        echo json_encode(['success' => false, 'message' => 'Token inválido ou expirado.']);
        exit;
    }
    if (strtotime($usuario['token_expira']) < time()) {
        error_log("[resetar_senha] Token expirado para usuário ID: " . $usuario['id']);
        echo json_encode(['success' => false, 'message' => 'Token expirado. Solicite uma nova recuperação.']);
        exit;
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE usuarios SET senha = ?, token_recuperacao = NULL, token_expira = NULL WHERE id = ?');
    $stmt->execute([$senhaHash, $usuario['id']]);

    error_log("[resetar_senha] Senha redefinida com sucesso para usuário ID: " . $usuario['id']);
    echo json_encode(['success' => true, 'message' => 'Senha redefinida com sucesso!']);
} catch (Exception $e) {
    error_log("[resetar_senha] Erro ao redefinir senha: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao redefinir senha.']);
} 
