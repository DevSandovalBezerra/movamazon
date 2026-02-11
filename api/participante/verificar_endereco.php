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
    
    $stmt = $pdo->prepare("
        SELECT cep, endereco, numero, bairro, cidade, uf 
        FROM usuarios 
        WHERE id = ?
    ");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        throw new Exception('Usuário não encontrado');
    }
    
    // Limpar e validar campos
    // CEP: pode vir formatado (00000-000) ou não, precisa limpar
    $cep_raw = $usuario['cep'] ?? '';
    $cep = preg_replace('/[^0-9]/', '', $cep_raw);
    
    // Endereço: remover espaços extras e verificar se não é NULL
    // Usar null coalescing operator para tratar NULL como string vazia
    $endereco = trim((string)($usuario['endereco'] ?? ''));
    $numero = trim((string)($usuario['numero'] ?? ''));
    $bairro = trim((string)($usuario['bairro'] ?? ''));
    $cidade = trim((string)($usuario['cidade'] ?? ''));
    $uf = strtoupper(trim((string)($usuario['uf'] ?? '')));
    
    // Debug: Log dos valores recebidos do banco
    error_log("VERIFICAR_ENDERECO - Usuario ID: $usuario_id");
    error_log("VERIFICAR_ENDERECO - CEP: " . ($usuario['cep'] ?? 'NULL') . " -> Limpo: '$cep'");
    error_log("VERIFICAR_ENDERECO - Endereco: " . ($usuario['endereco'] ?? 'NULL') . " -> Trim: '$endereco'");
    error_log("VERIFICAR_ENDERECO - Numero: " . ($usuario['numero'] ?? 'NULL') . " -> Trim: '$numero'");
    error_log("VERIFICAR_ENDERECO - Bairro: " . ($usuario['bairro'] ?? 'NULL') . " -> Trim: '$bairro'");
    error_log("VERIFICAR_ENDERECO - Cidade: " . ($usuario['cidade'] ?? 'NULL') . " -> Trim: '$cidade'");
    error_log("VERIFICAR_ENDERECO - UF: " . ($usuario['uf'] ?? 'NULL') . " -> Trim: '$uf'");
    
    // Lista de campos obrigatórios
    $campos_obrigatorios = [
        'cep' => ['valor' => $cep, 'valido' => !empty($cep) && strlen($cep) === 8],
        'endereco' => ['valor' => $endereco, 'valido' => !empty($endereco)],
        'numero' => ['valor' => $numero, 'valido' => !empty($numero)],
        'bairro' => ['valor' => $bairro, 'valido' => !empty($bairro)],
        'cidade' => ['valor' => $cidade, 'valido' => !empty($cidade)],
        'uf' => ['valor' => $uf, 'valido' => !empty($uf) && strlen($uf) === 2]
    ];
    
    // Verificar quais campos estão faltando
    $campos_faltando = [];
    foreach ($campos_obrigatorios as $campo => $dados) {
        if (!$dados['valido']) {
            $campos_faltando[] = $campo;
            error_log("VERIFICAR_ENDERECO - Campo faltando: $campo (valor: '" . $dados['valor'] . "')");
        }
    }
    
    $endereco_completo = empty($campos_faltando);
    
    error_log("VERIFICAR_ENDERECO - Resultado: endereco_completo=" . ($endereco_completo ? 'true' : 'false') . ", campos_faltando=" . json_encode($campos_faltando));
    
    echo json_encode([
        'success' => true,
        'endereco_completo' => $endereco_completo,
        'campos_faltando' => $campos_faltando,
        'dados' => [
            'cep' => $cep ? substr($cep, 0, 5) . '-***' : null,
            'endereco' => $endereco ? substr($endereco, 0, 20) . '...' : null,
            'cidade' => $cidade ?: null,
            'uf' => $uf ?: null
        ],
        'debug' => [
            'cep_valido' => !empty($cep) && strlen($cep) === 8,
            'endereco_valido' => !empty($endereco),
            'numero_valido' => !empty($numero),
            'bairro_valido' => !empty($bairro),
            'cidade_valido' => !empty($cidade),
            'uf_valido' => !empty($uf) && strlen($uf) === 2
        ]
    ]);
} catch (Exception $e) {
    error_log("Erro ao verificar endereço: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao verificar endereço: ' . $e->getMessage()]);
}
?>

