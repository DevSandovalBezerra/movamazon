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

// Verificar ID
$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID da camisa não fornecido']);
    exit();
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];

    // Buscar camisa e verificar permissão
    $stmt = $pdo->prepare('
        SELECT c.*, p.nome as produto_nome
        FROM camisas c
        LEFT JOIN produtos p ON c.produto_id = p.id
        WHERE c.id = ? AND c.evento_id IN (SELECT id FROM eventos WHERE (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL)
    ');
    $stmt->execute([$id, $organizador_id, $usuario_id]);
    $camisa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$camisa) {
        echo json_encode(['success' => false, 'message' => 'Camisa não encontrada ou sem permissão']);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'camisa' => $camisa
    ]);
    
} catch (Exception $e) {
    error_log('Erro ao buscar camisa: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 
