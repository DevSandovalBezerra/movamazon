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

$payload = json_decode(file_get_contents('php://input'), true) ?? [];

$id = isset($payload['id']) ? (int) $payload['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$nome = isset($payload['nome_completo']) ? trim($payload['nome_completo']) : '';
$email = isset($payload['email']) ? trim($payload['email']) : '';
$telefone = isset($payload['telefone']) ? trim($payload['telefone']) : '';
$celular = isset($payload['celular']) ? trim($payload['celular']) : '';
$empresa = isset($payload['empresa']) ? trim($payload['empresa']) : '';
$regiao = isset($payload['regiao']) ? trim($payload['regiao']) : '';
$modalidade = isset($payload['modalidade_esportiva']) ? trim($payload['modalidade_esportiva']) : '';
$quantidadeEventos = isset($payload['quantidade_eventos']) ? trim($payload['quantidade_eventos']) : '';
$regulamento = isset($payload['regulamento']) ? trim($payload['regulamento']) : '';

if (empty($nome) || empty($email) || empty($empresa) || empty($regiao) || empty($modalidade)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Campos obrigatórios: nome, email, empresa, região e modalidade']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email AND id != :id LIMIT 1");
    $stmtCheck->execute(['email' => $email, 'id' => $id]);
    if ($stmtCheck->fetch()) {
        throw new RuntimeException('Email já cadastrado para outro usuário');
    }

    $stmtUser = $pdo->prepare("
        UPDATE usuarios SET
            nome_completo = :nome,
            email = :email,
            telefone = :telefone,
            celular = :celular
        WHERE id = :id
    ");
    $stmtUser->execute([
        'nome' => $nome,
        'email' => $email,
        'telefone' => $telefone ?: null,
        'celular' => $celular ?: null,
        'id' => $id
    ]);

    $stmtOrg = $pdo->prepare("SELECT id FROM organizadores WHERE usuario_id = :id LIMIT 1");
    $stmtOrg->execute(['id' => $id]);
    $organizador = $stmtOrg->fetch(PDO::FETCH_ASSOC);

    if ($organizador) {
        $stmtUpdateOrg = $pdo->prepare("
            UPDATE organizadores SET
                empresa = :empresa,
                regiao = :regiao,
                modalidade_esportiva = :modalidade,
                quantidade_eventos = :quantidade,
                regulamento = :regulamento
            WHERE usuario_id = :id
        ");
        $stmtUpdateOrg->execute([
            'empresa' => $empresa,
            'regiao' => $regiao,
            'modalidade' => $modalidade,
            'quantidade' => $quantidadeEventos ?: null,
            'regulamento' => $regulamento ?: null,
            'id' => $id
        ]);
    } else {
        $stmtInsertOrg = $pdo->prepare("
            INSERT INTO organizadores (
                usuario_id, empresa, regiao, modalidade_esportiva,
                quantidade_eventos, regulamento
            ) VALUES (
                :usuario_id, :empresa, :regiao, :modalidade,
                :quantidade, :regulamento
            )
        ");
        $stmtInsertOrg->execute([
            'usuario_id' => $id,
            'empresa' => $empresa,
            'regiao' => $regiao,
            'modalidade' => $modalidade,
            'quantidade' => $quantidadeEventos ?: null,
            'regulamento' => $regulamento ?: null
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Organizador atualizado com sucesso'
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[ADMIN_ORGANIZADORES_UPDATE] ' . $e->getMessage());
    http_response_code(500);
    $message = $e->getMessage() === 'Email já cadastrado para outro usuário' ? 'Email já cadastrado para outro usuário' : 'Erro ao atualizar organizador';
    echo json_encode(['success' => false, 'message' => $message]);
}

