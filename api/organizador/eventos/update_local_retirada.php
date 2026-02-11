<?php
session_start();
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

header('Content-Type: application/json');

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

$local_id = $input['id'] ?? null;
$evento_id = $input['evento_id'] ?? null;
$data_retirada = $input['data_retirada'] ?? null;
$horario_inicio = $input['horario_inicio'] ?? null;
$horario_fim = $input['horario_fim'] ?? null;
$local_retirada = $input['local_retirada'] ?? null;

if (!$local_id || !$evento_id || !$data_retirada || !$horario_inicio || !$horario_fim || !$local_retirada) {
    echo json_encode(['success' => false, 'message' => 'Todos os campos obrigatórios devem ser preenchidos']);
    exit;
}

try {
    // Verificar se o local pertence a um evento do organizador
    $stmt = $pdo->prepare("
        SELECT rk.id 
        FROM retirada_kits_evento rk
        INNER JOIN eventos e ON rk.evento_id = e.id
        WHERE rk.id = ? AND (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL
    ");
    $stmt->execute([$local_id, $organizador_id, $usuario_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Local não encontrado ou não autorizado']);
        exit;
    }
    
    // Atualizar local de retirada
    $stmt = $pdo->prepare("
        UPDATE retirada_kits_evento SET
            data_retirada = ?,
            horario_inicio = ?,
            horario_fim = ?,
            local_retirada = ?,
            endereco_completo = ?,
            instrucoes_retirada = ?,
            retirada_terceiros = ?,
            documentos_necessarios = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $data_retirada,
        $horario_inicio,
        $horario_fim,
        $local_retirada,
        $input['endereco_completo'] ?? '',
        $input['instrucoes_retirada'] ?? '',
        $input['retirada_terceiros'] ?? '',
        $input['documentos_necessarios'] ?? '',
        $local_id
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Local de retirada atualizado com sucesso'
    ]);
    
} catch (PDOException $e) {
    error_log('Erro ao atualizar local de retirada: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
