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
    $email = trim($input['email'] ?? '');
    $senha = $input['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        throw new Exception('E-mail e senha são obrigatórios');
    }

    $stmt = $pdo->prepare("
        SELECT id, nome_completo, email, senha, status 
        FROM usuarios 
        WHERE email = ? AND status = 'ativo' 
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        throw new Exception('Usuário não encontrado ou inativo');
    }

    if (!password_verify($senha, $usuario['senha'])) {
        throw new Exception('E-mail ou senha inválidos');
    }

    // Verificar se tem papel de assessoria
    $stmt = $pdo->prepare("
        SELECT p.nome as papel_nome
        FROM usuario_papeis up
        JOIN papeis p ON up.papel_id = p.id
        WHERE up.usuario_id = ? AND p.nome IN ('assessoria_admin', 'assessor')
    ");
    $stmt->execute([$usuario['id']]);
    $papeis_assessoria = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($papeis_assessoria)) {
        throw new Exception('Você não possui acesso à área de assessoria. Cadastre-se primeiro.');
    }

    // Buscar dados da assessoria vinculada
    $stmt = $pdo->prepare("
        SELECT ae.assessoria_id, ae.funcao, a.nome_fantasia, a.status as assessoria_status
        FROM assessoria_equipe ae
        JOIN assessorias a ON ae.assessoria_id = a.id
        WHERE ae.usuario_id = ? AND ae.status = 'ativo'
        LIMIT 1
    ");
    $stmt->execute([$usuario['id']]);
    $equipe = $stmt->fetch(PDO::FETCH_ASSOC);

    // Determinar papel principal da assessoria
    $papel_assessoria = in_array('assessoria_admin', $papeis_assessoria) ? 'assessoria_admin' : 'assessor';

    // Criar sessao
    $_SESSION['user_id'] = $usuario['id'];
    $_SESSION['user_email'] = $usuario['email'];
    $_SESSION['user_name'] = $usuario['nome_completo'];
    $_SESSION['papel'] = $papel_assessoria;
    $_SESSION['user_papeis'] = $papeis_assessoria;
    $_SESSION['assessoria_id'] = $equipe ? (int) $equipe['assessoria_id'] : null;
    $_SESSION['assessoria_funcao'] = $equipe ? $equipe['funcao'] : null;
    $_SESSION['login_time'] = time();

    error_log("[ASSESSORIA_LOGIN] Login: {$usuario['email']} (ID: {$usuario['id']}, Papel: {$papel_assessoria})");

    echo json_encode([
        'success' => true,
        'message' => 'Login realizado com sucesso',
        'usuario' => [
            'id' => $usuario['id'],
            'nome' => $usuario['nome_completo'],
            'email' => $usuario['email'],
            'papel' => $papel_assessoria
        ],
        'assessoria' => $equipe ? [
            'id' => (int) $equipe['assessoria_id'],
            'nome' => $equipe['nome_fantasia'],
            'funcao' => $equipe['funcao'],
            'status' => $equipe['assessoria_status']
        ] : null,
        'redirect' => '../../frontend/paginas/assessoria/index.php?page=dashboard'
    ]);

} catch (Exception $e) {
    error_log("[ASSESSORIA_LOGIN] Erro: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
