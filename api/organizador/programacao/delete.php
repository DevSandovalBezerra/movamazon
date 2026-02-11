<?php
header('Content-Type: application/json');
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['papel']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Acesso negado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    $data = json_decode(file_get_contents('php://input'), true);
    
    $programacao_id = $data['id'] ?? null;
    
    if (!$programacao_id) {
        echo json_encode(['success' => false, 'error' => 'ID da programação é obrigatório']);
        exit;
    }
    
    // Verificar se o item de programação pertence ao organizador
    $sql = "SELECT pe.id FROM programacao_evento pe 
            INNER JOIN eventos e ON pe.evento_id = e.id 
            WHERE pe.id = ? AND (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$programacao_id, $organizador_id, $usuario_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Item de programação não encontrado ou sem permissão']);
        exit;
    }
    
    // Excluir item de programação
    $sql = "DELETE FROM programacao_evento WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$programacao_id]);
    
    echo json_encode(['success' => true, 'message' => 'Item de programação excluído com sucesso']);
    
} catch (Exception $e) {
    http_response_code(500);
    error_log("Erro em programacao/delete.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
?>
