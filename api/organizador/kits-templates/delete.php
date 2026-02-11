<?php
session_start();
require_once '../../db.php';

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
    $organizador_id = $_SESSION['user_id'];
    
    // Receber dados do JSON
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID do template é obrigatório']);
        exit();
    }
    
    // Verificar se o template existe
    $stmt = $pdo->prepare("SELECT id, nome, foto_kit FROM kit_templates WHERE id = ?");
    $stmt->execute([$id]);
    $template = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$template) {
        echo json_encode(['success' => false, 'error' => 'Template não encontrado']);
        exit();
    }
    
    // Verificar se o template está sendo usado em kits (ativo ou inativo)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_uso
        FROM kits_eventos 
        WHERE kit_template_id = ?
    ");
    $stmt->execute([$id]);
    $uso = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($uso['total_uso'] > 0) {
        echo json_encode([
            'success' => false, 
            'error' => 'Não é possível excluir este template pois ele está sendo usado em kits de eventos'
        ]);
        exit();
    }
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    try {
        // Deletar produtos do template
        $stmt = $pdo->prepare("DELETE FROM kit_template_produtos WHERE kit_template_id = ?");
        $stmt->execute([$id]);
        
        // Remover foto se existir
        if ($template['foto_kit']) {
            $caminhoFoto1 = '../../assets/img/kits/' . $template['foto_kit'];
            $caminhoFoto2 = '../../../' . $template['foto_kit'];
            if (file_exists($caminhoFoto1)) {
                unlink($caminhoFoto1);
            } elseif (file_exists($caminhoFoto2)) {
                unlink($caminhoFoto2);
            }
        }
        
        // Deletar o template (hard delete)
        $stmt = $pdo->prepare("DELETE FROM kit_templates WHERE id = ?");
        $resultado = $stmt->execute([$id]);
        
        if ($resultado) {
            // Commit da transação
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Template excluído com sucesso!'
            ]);
        } else {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'Erro ao excluir template']);
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    // Rollback em caso de erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}
?> 
