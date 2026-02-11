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

    $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
    $stmtCheck->execute(['email' => $email]);
    if ($stmtCheck->fetch()) {
        throw new RuntimeException('Email já cadastrado');
    }

    $senhaTemporaria = bin2hex(random_bytes(4));
    $hash = password_hash($senhaTemporaria, PASSWORD_DEFAULT);

    $stmtUser = $pdo->prepare("
        INSERT INTO usuarios (
            nome_completo, email, senha, telefone, celular, 
            pais, status, papel, data_cadastro
        ) VALUES (
            :nome, :email, :senha, :telefone, :celular,
            'Brasil', 'ativo', 'organizador', NOW()
        )
    ");
    $stmtUser->execute([
        'nome' => $nome,
        'email' => $email,
        'senha' => $hash,
        'telefone' => $telefone ?: null,
        'celular' => $celular ?: null
    ]);
    $usuarioId = $pdo->lastInsertId();

    $stmtOrg = $pdo->prepare("
        INSERT INTO organizadores (
            usuario_id, empresa, regiao, modalidade_esportiva,
            quantidade_eventos, regulamento
        ) VALUES (
            :usuario_id, :empresa, :regiao, :modalidade,
            :quantidade, :regulamento
        )
    ");
    $stmtOrg->execute([
        'usuario_id' => $usuarioId,
        'empresa' => $empresa,
        'regiao' => $regiao,
        'modalidade' => $modalidade,
        'quantidade' => $quantidadeEventos ?: null,
        'regulamento' => $regulamento ?: null
    ]);

    $stmtPapel = $pdo->prepare("SELECT id FROM papeis WHERE nome = 'organizador' LIMIT 1");
    $stmtPapel->execute();
    $papel = $stmtPapel->fetch(PDO::FETCH_ASSOC);

    if ($papel) {
        $stmtInsertPapel = $pdo->prepare("INSERT INTO usuario_papeis (usuario_id, papel_id) VALUES (:usuario, :papel)");
        $stmtInsertPapel->execute(['usuario' => $usuarioId, 'papel' => $papel['id']]);
    }

    $pdo->commit();

    $baseUrl = envValue('APP_URL', '');
    if ($baseUrl === '' && isset($_SERVER['HTTP_HOST'])) {
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
    }
    $loginUrl = rtrim($baseUrl, '/') . '/frontend/paginas/auth/login.php';

    $html = '
        <p>Olá ' . htmlspecialchars($nome) . ',</p>
        <p>Você foi cadastrado como organizador no MovAmazonas.</p>
        <p>Use as credenciais abaixo para acessar o painel:</p>
        <ul>
            <li><strong>Login:</strong> ' . htmlspecialchars($email) . '</li>
            <li><strong>Senha temporária:</strong> ' . $senhaTemporaria . '</li>
        </ul>
        <p>Faça login em: <a href="' . $loginUrl . '">' . $loginUrl . '</a> e altere a senha assim que possível.</p>
        <p>Equipe MovAmazonas.</p>
    ';

    sendEmail($email, 'Cadastro como Organizador - MovAmazonas', $html);

    echo json_encode([
        'success' => true,
        'message' => 'Organizador criado com sucesso',
        'data' => [
            'id' => (int) $usuarioId,
            'email' => $email,
            'senha_temporaria' => $senhaTemporaria
        ]
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[ADMIN_ORGANIZADORES_CREATE] ' . $e->getMessage());
    http_response_code(500);
    $message = $e->getMessage() === 'Email já cadastrado' ? 'Email já cadastrado' : 'Erro ao criar organizador';
    echo json_encode(['success' => false, 'message' => $message]);
}

