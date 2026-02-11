<?php
header('Content-Type: application/json');
require_once '../db.php';

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

// Verificar se o usuário está logado como organizador
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit();
}

try {
    // Validar campos obrigatórios
    if (!isset($_POST['evento_id']) || empty($_POST['evento_id'])) {
        echo json_encode(['success' => false, 'message' => 'ID do evento é obrigatório']);
        exit();
    }
    
    $evento_id = (int)$_POST['evento_id'];
    $organizador_id = $_SESSION['user_id'];
    
    // Verificar se o evento existe e pertence ao organizador
    $stmt = $pdo->prepare("SELECT id, nome FROM eventos WHERE id = ? AND organizador_id = ?");
    $stmt->execute([$evento_id, $organizador_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$evento) {
        echo json_encode(['success' => false, 'message' => 'Evento não encontrado ou não pertence a você']);
        exit();
    }
    
    // Verificar se há inscrições no evento
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM inscricoes WHERE evento_id = ?");
    $stmt->execute([$evento_id]);
    $inscricoes = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($inscricoes['total'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Não é possível excluir um evento que possui inscrições']);
        exit();
    }
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    try {
        // Excluir lotes do evento
        $stmt = $pdo->prepare("DELETE FROM lotes WHERE evento_id = ?");
        $stmt->execute([$evento_id]);
        
        // Excluir kits por modalidade do evento
        $stmt = $pdo->prepare("DELETE FROM kits_modalidades WHERE evento_id = ?");
        $stmt->execute([$evento_id]);
        
        // Excluir produtos extras do evento
        $stmt = $pdo->prepare("DELETE FROM produtos_extras WHERE evento_id = ?");
        $stmt->execute([$evento_id]);
        
        // Excluir tamanhos de camisetas do evento
        $stmt = $pdo->prepare("DELETE FROM tamanhos_camisetas WHERE evento_id = ?");
        $stmt->execute([$evento_id]);
        
        // Excluir o evento
        $stmt = $pdo->prepare("DELETE FROM eventos WHERE id = ? AND organizador_id = ?");
        $resultado = $stmt->execute([$evento_id, $organizador_id]);
        
        if ($resultado) {
            // Confirmar transação
            $pdo->commit();
            
            // Log da exclusão
            error_log("Evento excluído - ID: $evento_id, Nome: {$evento['nome']}, Organizador: $organizador_id");
            
            echo json_encode([
                'success' => true, 
                'message' => 'Evento excluído com sucesso'
            ]);
        } else {
            // Reverter transação
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir evento']);
        }
        
    } catch (Exception $e) {
        // Reverter transação em caso de erro
        $pdo->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Erro ao excluir evento: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
} catch (Exception $e) {
    error_log("Erro inesperado ao excluir evento: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro inesperado']);
}
?> 
