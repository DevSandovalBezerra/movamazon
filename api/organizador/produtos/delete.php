<?php
session_start();
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

// Verificar se o usuário está logado como organizador
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit();
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $organizador_id = $ctx['organizador_id'];
    
    // Receber dados do JSON
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID do produto é obrigatório']);
        exit();
    }
    
    // Verificar se o produto existe e está ativo
    $stmt = $pdo->prepare("SELECT id, nome, foto_produto FROM produtos WHERE id = ? AND ativo = 1");
    $stmt->execute([$id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$produto) {
        echo json_encode(['success' => false, 'error' => 'Produto não encontrado']);
        exit();
    }
    
    // Verificar se o produto está sendo usado em kits
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_uso
        FROM (
            SELECT kp.id FROM kit_produtos kp WHERE kp.produto_id = ? AND kp.ativo = 1
            UNION ALL
            SELECT ktp.id FROM kit_template_produtos ktp WHERE ktp.produto_id = ? AND ktp.ativo = 1
        ) as usos
    ");
    $stmt->execute([$id, $id]);
    $uso = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($uso['total_uso'] > 0) {
        echo json_encode([
            'success' => false, 
            'error' => 'Não é possível excluir este produto pois ele está sendo usado em kits ou templates'
        ]);
        exit();
    }
    
    // Soft delete - marcar como inativo
    $stmt = $pdo->prepare("UPDATE produtos SET ativo = 0, updated_at = NOW() WHERE id = ?");
    $resultado = $stmt->execute([$id]);
    
    if ($resultado) {
        // Remover foto se existir
        if ($produto['foto_produto'] && file_exists('../../../' . $produto['foto_produto'])) {
            unlink('../../../' . $produto['foto_produto']);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Produto excluído com sucesso!'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erro ao excluir produto']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}
?> 
