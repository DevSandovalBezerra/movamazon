<?php
    session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../db.php';

error_log('=== INÍCIO API LOGIN ===');
error_log('Timestamp: ' . date('Y-m-d H:i:s'));
error_log('Método: ' . $_SERVER['REQUEST_METHOD']);
error_log('Content-Type: ' . ($_SERVER['CONTENT_TYPE'] ?? 'não definido'));

try {
    $input = json_decode(file_get_contents('php://input'), true);
    error_log('Input JSON recebido: ' . json_encode($input));

    $identificacao = $input['email'] ?? '';
    $senha = $input['senha'] ?? '';

    error_log('- Senha: [OCULTA] (tamanho: ' . strlen($senha ?? '') . ')');

    if (empty($identificacao) || empty($senha)) {
        error_log('ERRO: Identificação ou senha vazios');
        throw new Exception('Email e senha são obrigatórios');
    }

    $sql = "
        SELECT
            id,
            nome_completo,
            email,
            senha,
            data_nascimento,
            documento,
            telefone,
            celular,
            status,
            papel
        FROM usuarios
        WHERE email = ?
        AND status = 'ativo'
        LIMIT 1
    ";

    error_log('Parâmetros: [' . $identificacao . '');

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$identificacao]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        error_log('ERRO: Usuário não encontrado para identificação: ' . $identificacao);
        throw new Exception('Usuário não encontrado ou inativo');
    }

    error_log('Usuário encontrado - ID: ' . $usuario['id'] . ', Nome: ' . $usuario['nome_completo']);

    if (!password_verify($senha, $usuario['senha'])) {
        error_log('ERRO: Senha incorreta para usuário ID: ' . $usuario['id']);
        throw new Exception('Email ou senha inválidos.');
    }
    error_log('Senha verificada com sucesso!');

    unset($usuario['senha']);

    $_SESSION['user_id'] = $usuario['id'];
    $_SESSION['user_name'] = $usuario['nome_completo'];
    $_SESSION['user_email'] = $usuario['email'];
    $_SESSION['papel'] = $usuario['papel'] ?? 'participante';
    $_SESSION['login_time'] = time();
    
    error_log('- user_id: ' . $_SESSION['user_id']);
    $response = [
        'success' => true,
        'message' => 'Login bem-sucedido!',
        'user_type' => $usuario['papel'] ?? 'participante',
        'usuario' => $usuario
    ];
    
    error_log('=== FIM API LOGIN - SUCESSO ===');
    echo json_encode($response);
} catch (Exception $e) {
    error_log('ERRO no login: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    http_response_code(400);
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    
    error_log('=== FIM API LOGIN - ERRO ===');
    error_log('Resposta: ' . json_encode($response));
    echo json_encode($response);
}
?>

