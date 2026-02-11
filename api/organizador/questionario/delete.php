<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../auth/auth.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

// Verificar autenticação
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID da pergunta é obrigatório']);
        exit;
    }

    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];

    // Verificar se a pergunta existe e pertence ao organizador (novo + legado)
    $stmt = $pdo->prepare("
        SELECT qe.id, qe.evento_id, qe.ordem 
        FROM questionario_evento qe 
        INNER JOIN eventos e ON qe.evento_id = e.id 
        WHERE qe.id = ? AND (e.organizador_id = ? OR e.organizador_id = ?)
    ");
    $stmt->execute([$id, $organizador_id, $usuario_id]);
    $pergunta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pergunta) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Pergunta não encontrada ou acesso negado']);
        exit;
    }

    $pdo->beginTransaction();

    // Excluir associações com modalidades (CASCADE já faz isso, mas explicitando)
    $stmt = $pdo->prepare("DELETE FROM questionario_evento_modalidade WHERE questionario_evento_id = ?");
    $stmt->execute([$id]);

    // Excluir pergunta/campo
    $stmt = $pdo->prepare("DELETE FROM questionario_evento WHERE id = ?");
    $stmt->execute([$id]);

    // Reorganizar ordem das perguntas restantes
    $stmt = $pdo->prepare("
        UPDATE questionario_evento 
        SET ordem = ordem - 1 
        WHERE evento_id = ? AND ordem > ?
    ");
    $stmt->execute([$pergunta['evento_id'], $pergunta['ordem']]);

    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Pergunta/campo excluído com sucesso'
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    error_log('Erro ao excluir pergunta: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 
