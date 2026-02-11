<?php
session_start();
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

// Verificar autenticação
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit();
}

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

// Ler dados do corpo da requisição
$input = json_decode(file_get_contents('php://input'), true);

// Validar ID
$id = $input['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID da camisa não fornecido']);
    exit();
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];

    // Verificar se a camisa pertence ao organizador
    $stmt = $pdo->prepare('
        SELECT c.id, c.quantidade_vendida, c.quantidade_reservada
        FROM camisas c
        WHERE c.id = ? AND c.evento_id IN (SELECT id FROM eventos WHERE (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL)
    ');
    $stmt->execute([$id, $organizador_id, $usuario_id]);
    $camisa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$camisa) {
        echo json_encode(['success' => false, 'message' => 'Camisa não encontrada ou sem permissão']);
        exit();
    }
    
    // Verificar se há vendas ou reservas
    if ($camisa['quantidade_vendida'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Não é possível excluir um tamanho que já possui vendas']);
        exit();
    }
    
    if ($camisa['quantidade_reservada'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Não é possível excluir um tamanho que possui reservas']);
        exit();
    }
    
    // Excluir camisa
    $stmt = $pdo->prepare('DELETE FROM camisas WHERE id = ?');
    $stmt->execute([$id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Tamanho excluído com sucesso'
    ]);
    
} catch (Exception $e) {
    error_log('Erro ao excluir camisa: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 
