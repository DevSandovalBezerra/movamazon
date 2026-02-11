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

$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID do cupom não fornecido']);
    exit;
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    
    $stmt = $pdo->prepare('SELECT id, evento_id FROM cupons_remessa WHERE id = ?');
    $stmt->execute([$id]);
    $remessa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$remessa) {
        echo json_encode(['success' => false, 'message' => 'Cupom não encontrado']);
        exit;
    }
    
    if ($remessa['evento_id']) {
        $stmt = $pdo->prepare('SELECT id FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL');
        $stmt->execute([$remessa['evento_id'], $organizador_id, $usuario_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Sem permissão para este cupom']);
            exit;
        }
    }
    
    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM inscricoes_cupons WHERE cupom_id = ?');
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $utilizacoes = (int)($result['total'] ?? 0);
    
    echo json_encode([
        'success' => true,
        'utilizacoes' => $utilizacoes
    ]);
    
} catch (Exception $e) {
    error_log('Erro ao verificar utilizações do cupom: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 
