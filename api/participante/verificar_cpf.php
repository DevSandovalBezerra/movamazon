<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

$usuario_id = $_SESSION['user_id'];

try {
    if (!isset($pdo)) {
        throw new Exception('Conexão com banco de dados não disponível');
    }
    
    $stmt = $pdo->prepare("SELECT documento FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        throw new Exception('Usuário não encontrado');
    }
    
    $cpf = $usuario['documento'] ?? '';
    $cpf_limpo = preg_replace('/[^0-9]/', '', $cpf);
    
    echo json_encode([
        'success' => true,
        'tem_cpf' => !empty($cpf_limpo) && strlen($cpf_limpo) === 11,
        'cpf' => $cpf_limpo ? substr($cpf_limpo, 0, 3) . '.***.***-**' : null
    ]);
} catch (Exception $e) {
    error_log("Erro ao verificar CPF: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao verificar CPF: ' . $e->getMessage()]);
}
?>

