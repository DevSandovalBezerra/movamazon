<?php
session_start();
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) && isset($_SESSION['usuario_id'])) {
    $_SESSION['user_id'] = $_SESSION['usuario_id'];
}

if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$ctx = requireOrganizadorContext($pdo);
$usuario_id = $ctx['usuario_id'];
$organizador_id = $ctx['organizador_id'];
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

$evento_id = $input['evento_id'] ?? null;
$data_retirada = $input['data_retirada'] ?? null;
$horario_inicio = $input['horario_inicio'] ?? null;
$horario_fim = $input['horario_fim'] ?? null;
$local_retirada = $input['local_retirada'] ?? null;

if (!$evento_id || !$data_retirada || !$horario_inicio || !$horario_fim || !$local_retirada) {
    echo json_encode(['success' => false, 'message' => 'Todos os campos obrigatórios devem ser preenchidos']);
    exit;
}

try {
    // Verificar se o evento pertence ao organizador
    $stmt = $pdo->prepare("SELECT id FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL");
    $stmt->execute([$evento_id, $organizador_id, $usuario_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Evento não encontrado ou não autorizado']);
        exit;
    }
    
    // Inserir local de retirada
    $stmt = $pdo->prepare("
        INSERT INTO retirada_kits_evento (
            evento_id, data_retirada, horario_inicio, horario_fim, 
            local_retirada, endereco_completo, instrucoes_retirada, 
            retirada_terceiros, documentos_necessarios, ativo, data_criacao
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
    ");
    
    $stmt->execute([
        $evento_id,
        $data_retirada,
        $horario_inicio,
        $horario_fim,
        $local_retirada,
        $input['endereco_completo'] ?? '',
        $input['instrucoes_retirada'] ?? '',
        $input['retirada_terceiros'] ?? '',
        $input['documentos_necessarios'] ?? ''
    ]);
    
    $local_id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Local de retirada criado com sucesso',
        'local_id' => $local_id
    ]);
    
} catch (PDOException $e) {
    error_log('Erro ao criar local de retirada: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
