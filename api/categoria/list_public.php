<?php
header('Content-Type: application/json');
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

try {
    $evento_id = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : 0;
    if ($evento_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'evento_id é obrigatório']);
        exit();
    }

    $sql = "SELECT id, nome, descricao, tipo_publico, idade_min, idade_max
            FROM categorias
            WHERE ativo = 1 AND evento_id = ?
            ORDER BY nome";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$evento_id]);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'categorias' => $categorias
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao listar categorias públicas: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
