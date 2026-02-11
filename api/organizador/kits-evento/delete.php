<?php
session_start();
require_once '../../db.php';
require_once '../../helpers/organizador_context.php';
 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit();
}

try {
    // Usar contexto do organizador
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    
    // Receber dados do JSON
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID do kit é obrigatório']);
        exit();
    }
    
    // Verificar se o kit existe e pertence ao organizador
    $stmt = $pdo->prepare("
        SELECT k.id, k.nome, k.foto_kit, e.nome as evento_nome
        FROM kits_eventos k
        INNER JOIN eventos e ON k.evento_id = e.id
        WHERE k.id = ? AND (e.organizador_id = ? OR e.organizador_id = ?)
    ");
    $stmt->execute([$id, $organizador_id, $usuario_id]);
    $kit = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$kit) {
        echo json_encode(['success' => false, 'error' => 'Kit não encontrado ou não autorizado']);
        exit();
    }
    
    // Verificar se o kit está sendo usado em inscrições
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_uso
        FROM inscricoes 
        WHERE kit_id = ? 
    ");
    $stmt->execute([$id]);
    $uso = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($uso['total_uso'] > 0) {
        echo json_encode([
            'success' => false, 
            'error' => 'Não é possível excluir este kit pois existem inscrições associadas a ele'
        ]);
        exit();
    }
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    try {
        // Deletar produtos do kit
        $stmt = $pdo->prepare("DELETE FROM kit_produtos WHERE kit_id = ?");
        $stmt->execute([$id]);
        
        // Deletar modalidades associadas ao kit
        $stmt = $pdo->prepare("DELETE FROM kit_modalidade_evento WHERE kit_id = ?");
        $stmt->execute([$id]);
        
        // Remover foto se existir
        if ($kit['foto_kit'] && file_exists('../../assets/img/kits/' . $kit['foto_kit'])) {
            unlink('../../assets/img/kits/' . $kit['foto_kit']);
        }
        
        // Deletar o kit (hard delete)
        $stmt = $pdo->prepare("DELETE FROM kits_eventos WHERE id = ?");
        $resultado = $stmt->execute([$id]);
        
        if ($resultado) {
            // Commit da transação
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Kit excluído com sucesso!'
            ]);
        } else {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'Erro ao excluir kit']);
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
