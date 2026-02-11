<?php
header('Content-Type: application/json');
require_once '../../db.php';

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
    $modalidade_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if (!$modalidade_id) {
        echo json_encode(['success' => false, 'message' => 'ID da modalidade é obrigatório']);
        exit();
    }
    
    $pdo->beginTransaction();
    
    $sql = "UPDATE modalidades SET ativo = 0 WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute([$modalidade_id]);
    
    if ($resultado) {
        $pdo->commit();
        error_log("Modalidade desativada - ID: $modalidade_id");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Modalidade excluída com sucesso'
        ]);
    } else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir modalidade']);
    }
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erro ao excluir modalidade: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 
