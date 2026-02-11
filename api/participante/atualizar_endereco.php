<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

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

// Extrair e validar campos
$cep = isset($input['cep']) ? preg_replace('/[^0-9]/', '', trim($input['cep'])) : '';
$endereco = isset($input['endereco']) ? trim($input['endereco']) : '';
$numero = isset($input['numero']) ? trim($input['numero']) : '';
$bairro = isset($input['bairro']) ? trim($input['bairro']) : '';
$cidade = isset($input['cidade']) ? trim($input['cidade']) : '';
$uf = isset($input['uf']) ? strtoupper(trim($input['uf'])) : '';
$complemento = isset($input['complemento']) ? trim($input['complemento']) : null;

// Validar CEP (8 dígitos)
if (empty($cep) || strlen($cep) !== 8) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'CEP inválido. Deve conter 8 dígitos.']);
    exit;
}

// Validar endereço
if (empty($endereco)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Logradouro é obrigatório.']);
    exit;
}

// Validar número
if (empty($numero)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Número é obrigatório.']);
    exit;
}

// Validar bairro
if (empty($bairro)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Bairro é obrigatório.']);
    exit;
}

// Validar cidade
if (empty($cidade)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cidade é obrigatória.']);
    exit;
}

// Validar UF (2 caracteres, estados brasileiros)
$estados_brasileiros = [
    'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
    'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN',
    'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
];

if (empty($uf) || strlen($uf) !== 2 || !in_array($uf, $estados_brasileiros)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'UF inválida. Deve ser um estado brasileiro válido (2 letras).']);
    exit;
}

try {
    if (!isset($pdo)) {
        throw new Exception('Conexão com banco de dados não disponível');
    }
    
    // Atualizar endereço
    $stmt = $pdo->prepare("
        UPDATE usuarios 
        SET cep = ?, 
            endereco = ?, 
            numero = ?, 
            bairro = ?, 
            cidade = ?, 
            uf = ?,
            complemento = ?
        WHERE id = ?
    ");
    $stmt->execute([$cep, $endereco, $numero, $bairro, $cidade, $uf, $complemento, $usuario_id]);
    
    error_log("Endereço atualizado para usuário ID: $usuario_id");
    
    echo json_encode([
        'success' => true,
        'message' => 'Endereço atualizado com sucesso'
    ]);
} catch (Exception $e) {
    error_log("Erro ao atualizar endereço: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar endereço: ' . $e->getMessage()]);
}
?>

