<?php
header('Content-Type: application/json');
require_once '../db.php';
require_once __DIR__ . '/../helpers/organizador_context.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit();
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];

    $categoria_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if (!$categoria_id) {
        echo json_encode(['success' => false, 'message' => 'ID da categoria é obrigatório']);
        exit();
    }
    
    $pdo->beginTransaction();
    
    $sql = "UPDATE categorias c
            INNER JOIN eventos e ON e.id = c.evento_id
            SET c.ativo = 0
            WHERE c.id = ? AND (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL";
    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute([$categoria_id, $organizador_id, $usuario_id]);
    
    if ($resultado && $stmt->rowCount() > 0) {
        $pdo->commit();
        error_log("Categoria desativada - ID: $categoria_id");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Categoria excluída com sucesso'
        ]);
    } else {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Categoria não encontrada ou não pertence ao seu evento']);
    }
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erro ao excluir categoria: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
