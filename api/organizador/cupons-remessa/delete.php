<?php
session_start();
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID não informado']);
    exit;
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    
    $stmt = $pdo->prepare('SELECT * FROM cupons_remessa WHERE id = ?');
    $stmt->execute([$id]);
    $remessa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$remessa) {
        echo json_encode(['success' => false, 'message' => 'Remessa não encontrada']);
        exit;
    }
    
    if ($remessa['evento_id']) {
        $stmt = $pdo->prepare('SELECT id FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL');
        $stmt->execute([$remessa['evento_id'], $organizador_id, $usuario_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Sem permissão para esta remessa']);
            exit;
        }
    }
    
    $stmt = $pdo->prepare('DELETE FROM cupons_remessa WHERE id = ?');
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true, 'message' => 'Cupom excluído com sucesso']);
} catch (Exception $e) {
    error_log('Erro ao excluir remessa de cupons: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir remessa']);
} 
