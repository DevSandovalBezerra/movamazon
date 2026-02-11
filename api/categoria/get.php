<?php
header('Content-Type: application/json');
require_once '../db.php';
require_once __DIR__ . '/../helpers/organizador_context.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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

    $categoria_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $evento_id = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : 0;
    
    if (!$categoria_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID da categoria é obrigatório']);
        exit();
    }
    if ($evento_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'evento_id é obrigatório']);
        exit();
    }
    
    $sql = "SELECT c.id, c.nome, c.descricao, c.tipo_publico, c.idade_min, c.idade_max, c.desconto_idoso, c.ativo, c.data_criacao
            FROM categorias c
            INNER JOIN eventos e ON e.id = c.evento_id
            WHERE c.id = ? AND c.evento_id = ? AND (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$categoria_id, $evento_id, $organizador_id, $usuario_id]);
    $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$categoria) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Categoria não encontrada']);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'categoria' => $categoria
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar categoria: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
