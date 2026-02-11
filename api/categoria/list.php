<?php
header('Content-Type: application/json');
require_once '../db.php';
require_once '../middleware/auth.php';
require_once __DIR__ . '/../helpers/organizador_context.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

// Verificar autenticação usando middleware centralizado
verificarAutenticacao('organizador');

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];

    $evento_id = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : 0;
    if ($evento_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'evento_id é obrigatório']);
        exit();
    }

    // Verificar se o evento existe e não está excluído
    $stmt = $pdo->prepare("SELECT id, nome FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL");
    $stmt->execute([$evento_id, $organizador_id, $usuario_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$evento) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Evento não encontrado ou foi excluído']);
        exit();
    }

    $sql = "SELECT id, nome, descricao, tipo_publico, idade_min, idade_max, desconto_idoso, ativo, data_criacao
            FROM categorias
            WHERE evento_id = ?
            ORDER BY nome";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$evento_id]);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'categorias' => $categorias,
        'total' => count($categorias)
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao listar categorias: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
