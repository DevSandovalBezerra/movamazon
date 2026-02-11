<?php
session_start();
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

error_log('📡 API lotes/delete.php - Iniciando requisição');

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

// Verificar se o usuário está logado como organizador
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    error_log('❌ API lotes/delete.php - Usuário não autorizado');
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit();
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    
    // Validar campo obrigatório
    if (!isset($_POST['lote_id']) || empty($_POST['lote_id'])) {
        echo json_encode(['success' => false, 'message' => 'ID do lote é obrigatório']);
        exit();
    }
    
    $lote_id = (int)$_POST['lote_id'];
    
    error_log('📋 API lotes/delete.php - Excluindo lote ID: ' . $lote_id);
    
    // Verificar se o lote existe e pertence a um evento do organizador
    $stmt = $pdo->prepare("
        SELECT l.id_lote, e.organizador_id 
        FROM lotes l
        INNER JOIN modalidades m ON l.id_modalidade = m.id
        INNER JOIN eventos e ON m.evento_id = e.id
        WHERE l.id_lote = ? AND (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL
    ");
    $stmt->execute([$lote_id, $organizador_id, $usuario_id]);
    $lote = $stmt->fetch();
    
    if (!$lote) {
        error_log('❌ API lotes/delete.php - Lote não encontrado ou não autorizado');
        echo json_encode(['success' => false, 'message' => 'Lote não encontrado ou não autorizado']);
        exit();
    }
    
    // Verificar se há inscrições neste lote
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_inscritos 
        FROM inscricoes i
        INNER JOIN lotes l ON i.lote_inscricao_id = l.id_lote
        WHERE l.id_lote = ?
    ");
    $stmt->execute([$lote_id]);
    $inscricoes = $stmt->fetch();
    
    if ($inscricoes['total_inscritos'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Não é possível excluir um lote que possui inscrições']);
        exit();
    }
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    try {
        // Excluir preços do lote
        $stmt = $pdo->prepare("DELETE FROM lote_precos WHERE id_lote = ?");
        $stmt->execute([$lote_id]);
        
        // Excluir lote
        $stmt = $pdo->prepare("DELETE FROM lotes WHERE id_lote = ?");
        $resultado = $stmt->execute([$lote_id]);
        
        if ($resultado) {
            $pdo->commit();
            error_log('✅ API lotes/delete.php - Lote excluído com sucesso');
            echo json_encode([
                'success' => true, 
                'message' => 'Lote excluído com sucesso'
            ]);
        } else {
            throw new Exception('Erro ao excluir lote do banco de dados');
        }
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log('💥 API lotes/delete.php - Erro ao excluir lote: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 
