<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../security_middleware.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit();
}

$usuario_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT 
            id,
            nome_completo,
            email,
            telefone,
            celular,
            data_nascimento,
            endereco,
            numero,
            complemento,
            bairro,
            cidade,
            uf,
            cep,
            pais,
            sexo,
            documento,
            tipo_documento,
            foto_perfil
        FROM usuarios 
        WHERE id = ? AND status = 'ativo'
        LIMIT 1
    ");
    
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado.']);
        exit();
    }

    echo json_encode([
        'success' => true,
        'usuario' => $usuario
    ]);

} catch (Exception $e) {
    error_log("[GET_PERFIL] Erro: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar dados do perfil.']);
}

