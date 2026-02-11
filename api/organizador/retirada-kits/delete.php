<?php
header('Content-Type: application/json');
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticação
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
    
    $local_id = $data['id'] ?? null;
    
    if (!$local_id) {
        echo json_encode(['success' => false, 'error' => 'ID do local é obrigatório']);
        exit;
    }
    
    // Verificar se o local pertence ao organizador
    $sql_verificar = "SELECT rk.id 
                      FROM retirada_kits_evento rk 
                      INNER JOIN eventos e ON rk.evento_id = e.id 
                      WHERE rk.id = ? AND (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL";
    $stmt_verificar = $pdo->prepare($sql_verificar);
    $stmt_verificar->execute([$local_id, $organizador_id, $usuario_id]);
    
    if (!$stmt_verificar->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Local não encontrado ou sem permissão']);
        exit;
    }
    
    // Excluir local
    $sql = "DELETE FROM retirada_kits_evento WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$local_id]);
    
    echo json_encode(['success' => true, 'message' => 'Local de retirada excluído com sucesso']);
    
} catch (Exception $e) {
    http_response_code(500);
    error_log("Erro em delete.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
?>
