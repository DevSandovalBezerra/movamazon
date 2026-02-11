<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../db.php';
require_once __DIR__ . '/../helpers/organizador_context.php';

// Verificar se o usuário está logado como organizador
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit();
}

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

    // Buscar modalidades disponíveis
    $stmt = $pdo->prepare("SELECT id, nome, descricao FROM modalidades_novas WHERE ativo = TRUE ORDER BY nome");
    $stmt->execute();
    $modalidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar categorias disponíveis do evento
    $stmt = $pdo->prepare("SELECT id, nome, descricao FROM categorias WHERE ativo = TRUE AND evento_id = ? ORDER BY nome");
    $stmt->execute([$evento_id]);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'modalidades' => $modalidades,
        'categorias' => $categorias
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar opções: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
} catch (Exception $e) {
    error_log("Erro inesperado ao buscar opções: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro inesperado']);
}
?> 
