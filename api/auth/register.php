<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../db.php';

try {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        $data = $_POST ?: [];
    }

    $nome_completo = trim($data['nome_completo'] ?? $data['nome'] ?? '');
    $email = trim($data['email'] ?? '');
    $senha = (string)($data['senha'] ?? '');
    $papel = trim($data['papel'] ?? $data['tipo_usuario'] ?? 'participante');
    $documento = isset($data['documento']) ? preg_replace('/[^0-9]/', '', trim($data['documento'])) : null;
    
    // Extrair dados de endereço (obrigatórios a partir de agora)
    $cep = isset($data['cep']) ? preg_replace('/[^0-9]/', '', trim($data['cep'])) : null;
    $endereco = isset($data['endereco']) ? trim($data['endereco']) : null;
    $numero = isset($data['numero']) ? trim($data['numero']) : null;
    $complemento = isset($data['complemento']) ? trim($data['complemento']) : null;
    $bairro = isset($data['bairro']) ? trim($data['bairro']) : null;
    $cidade = isset($data['cidade']) ? trim($data['cidade']) : null;
    $uf = isset($data['uf']) ? strtoupper(trim($data['uf'])) : null;

    // Validação de campos básicos obrigatórios
    if ($nome_completo === '' || $email === '' || $senha === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Nome completo, e-mail e senha são obrigatórios.']);
        exit;
    }

    // Validar CPF (obrigatório - necessário para PIX e boleto)
    if (empty($documento) || strlen($documento) !== 11) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'CPF é obrigatório e deve conter 11 dígitos.']);
        exit;
    }
    
    // Validar CEP se fornecido (opcional, mas se fornecido deve ser válido)
    if ($cep && strlen($cep) !== 8) {
        $cep = null; // CEP inválido, não salvar
    }
    
    // Validar UF se fornecido (opcional, mas se fornecido deve ser válido)
    if ($uf && strlen($uf) !== 2) {
        $uf = null; // UF inválida, não salvar
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'E-mail inválido.']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Este e-mail já está cadastrado.']);
        exit;
    }

    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Construir SQL dinamicamente baseado nos campos fornecidos
    $campos = ['nome_completo', 'email', 'senha', 'papel', 'documento', 'tipo_documento'];
    $valores = [$nome_completo, $email, $senha_hash, $papel, $documento, 'CPF'];
    
    // Adicionar campos de endereço se fornecidos (opcionais no registro)
    if ($cep) {
        $campos[] = 'cep';
        $valores[] = $cep;
    }
    if ($endereco) {
        $campos[] = 'endereco';
        $valores[] = $endereco;
    }
    if ($numero) {
        $campos[] = 'numero';
        $valores[] = $numero;
    }
    if ($complemento) {
        $campos[] = 'complemento';
        $valores[] = $complemento;
    }
    if ($bairro) {
        $campos[] = 'bairro';
        $valores[] = $bairro;
    }
    if ($cidade) {
        $campos[] = 'cidade';
        $valores[] = $cidade;
    }
    if ($uf) {
        $campos[] = 'uf';
        $valores[] = $uf;
    }
    
    $campos[] = 'status';
    $valores[] = 'ativo';
    
    $placeholders = str_repeat('?,', count($valores) - 1) . '?';
    $sql = 'INSERT INTO usuarios (' . implode(', ', $campos) . ') VALUES (' . $placeholders . ')';
    $ins = $pdo->prepare($sql);
    $ins->execute($valores);

    echo json_encode(['success' => true, 'message' => 'Cadastro realizado com sucesso!']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao realizar o cadastro.']);
}
?>
