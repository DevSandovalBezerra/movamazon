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

if (!$input || !isset($input['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID do local é obrigatório']);
    exit;
}

$local_id = $input['id'];

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
    
    // Excluir local de retirada (soft delete)
    $stmt = $pdo->prepare("UPDATE retirada_kits_evento SET ativo = 0 WHERE id = ?");
    $stmt->execute([$local_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Local de retirada excluído com sucesso'
    ]);
    
} catch (PDOException $e) {
    error_log('Erro ao excluir local de retirada: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
