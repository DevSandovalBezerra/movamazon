<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $nome = trim($input['nome'] ?? '');
    $email = trim($input['email'] ?? '');
    $senha = $input['senha'] ?? '';
    $confirmar_senha = $input['confirmar_senha'] ?? '';
    $cref = trim($input['cref'] ?? '');
    $tipo = $input['tipo'] ?? 'PF';
    $cpf_cnpj = preg_replace('/[^0-9]/', '', $input['cpf_cnpj'] ?? '');
    $telefone = trim($input['telefone'] ?? '');
    $nome_fantasia = trim($input['nome_fantasia'] ?? '');

    // Validacoes
    if (empty($nome) || empty($email) || empty($senha)) {
        throw new Exception('Nome, e-mail e senha são obrigatórios');
    }
    if ($senha !== $confirmar_senha) {
        throw new Exception('As senhas não conferem');
    }
    if (strlen($senha) < 6) {
        throw new Exception('A senha deve ter no mínimo 6 caracteres');
    }
    if (empty($cref)) {
        throw new Exception('CREF é obrigatório para assessores');
    }
    if (!in_array($tipo, ['PF', 'PJ'])) {
        throw new Exception('Tipo deve ser PF ou PJ');
    }
    if (empty($cpf_cnpj)) {
        throw new Exception('CPF/CNPJ é obrigatório');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('E-mail inválido');
    }

    if (empty($nome_fantasia)) {
        $nome_fantasia = $nome;
    }

    // Verificar email unico
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception('Este e-mail já está cadastrado. Faça login.');
    }

    // Verificar CPF/CNPJ unico nas assessorias
    $stmt = $pdo->prepare("SELECT id FROM assessorias WHERE cpf_cnpj = ? LIMIT 1");
    $stmt->execute([$cpf_cnpj]);
    if ($stmt->fetch()) {
        throw new Exception('Já existe uma assessoria cadastrada com este CPF/CNPJ');
    }

    $pdo->beginTransaction();

    // 1. Criar usuario
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nome_completo, email, senha, telefone, papel, status, data_cadastro)
        VALUES (?, ?, ?, ?, 'participante', 'ativo', NOW())
    ");
    $stmt->execute([$nome, $email, $senha_hash, $telefone]);
    $usuario_id = (int) $pdo->lastInsertId();

    // 2. Buscar papel_id de assessoria_admin
    $stmt = $pdo->prepare("SELECT id FROM papeis WHERE nome = 'assessoria_admin' LIMIT 1");
    $stmt->execute();
    $papel = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$papel) {
        throw new Exception('Papel assessoria_admin não encontrado. Execute a migração SQL primeiro.');
    }

    // 3. Atribuir papel ao usuario
    $stmt = $pdo->prepare("
        INSERT INTO usuario_papeis (usuario_id, papel_id) VALUES (?, ?)
    ");
    $stmt->execute([$usuario_id, $papel['id']]);

    // 4. Criar assessoria
    $stmt = $pdo->prepare("
        INSERT INTO assessorias (tipo, nome_fantasia, cpf_cnpj, responsavel_usuario_id, 
                                 email_contato, telefone_contato, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 'ativo', NOW())
    ");
    $stmt->execute([$tipo, $nome_fantasia, $cpf_cnpj, $usuario_id, $email, $telefone]);
    $assessoria_id = (int) $pdo->lastInsertId();

    // 5. Vincular usuario na equipe como admin
    $stmt = $pdo->prepare("
        INSERT INTO assessoria_equipe (assessoria_id, usuario_id, funcao, status, created_at)
        VALUES (?, ?, 'admin', 'ativo', NOW())
    ");
    $stmt->execute([$assessoria_id, $usuario_id]);

    $pdo->commit();

    // Auto-login após cadastro
    $_SESSION['user_id'] = $usuario_id;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $nome;
    $_SESSION['papel'] = 'assessoria_admin';
    $_SESSION['user_papeis'] = ['assessoria_admin'];
    $_SESSION['assessoria_id'] = $assessoria_id;
    $_SESSION['assessoria_funcao'] = 'admin';
    $_SESSION['login_time'] = time();

    error_log("[ASSESSORIA_REGISTER] Novo cadastro: {$email} (UserID: {$usuario_id}, AssessoriaID: {$assessoria_id})");

    echo json_encode([
        'success' => true,
        'message' => 'Cadastro realizado com sucesso!',
        'usuario' => [
            'id' => $usuario_id,
            'nome' => $nome,
            'email' => $email
        ],
        'assessoria' => [
            'id' => $assessoria_id,
            'nome' => $nome_fantasia
        ],
        'redirect' => '../../frontend/paginas/assessoria/index.php?page=dashboard'
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("[ASSESSORIA_REGISTER] Erro: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
