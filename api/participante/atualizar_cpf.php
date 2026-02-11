<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

// Função de validação de CPF (definir antes de usar)
function validarCPF($cpf) {
    // Remover caracteres não numéricos
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    // Verificar se tem 11 dígitos
    if (strlen($cpf) != 11) {
        return false;
    }
    
    // Verificar se todos os dígitos são iguais
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
    // Validar dígitos verificadores
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    
    return true;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

$usuario_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$cpf = $input['cpf'] ?? '';

// Limpar CPF (remover pontos, traços, espaços)
$cpf_limpo = preg_replace('/[^0-9]/', '', $cpf);

// Validar CPF
if (empty($cpf_limpo) || strlen($cpf_limpo) !== 11) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'CPF inválido. Deve conter 11 dígitos.']);
    exit;
}

// Validar dígitos verificadores
if (!validarCPF($cpf_limpo)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'CPF inválido. Verifique os dígitos.']);
    exit;
}

try {
    if (!isset($pdo)) {
        throw new Exception('Conexão com banco de dados não disponível');
    }
    
    $stmt = $pdo->prepare("UPDATE usuarios SET documento = ? WHERE id = ?");
    $stmt->execute([$cpf_limpo, $usuario_id]);
    
    error_log("CPF atualizado para usuário ID: $usuario_id");
    
    echo json_encode([
        'success' => true,
        'message' => 'CPF atualizado com sucesso'
    ]);
} catch (Exception $e) {
    error_log("Erro ao atualizar CPF: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar CPF: ' . $e->getMessage()]);
}
?>

