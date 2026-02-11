<?php
header('Content-Type: application/json');
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticação
if (!isset($_SESSION['user_id']) || !isset($_SESSION['papel']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Acesso negado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    $data = json_decode(file_get_contents('php://input'), true);
    
    $evento_id = $data['evento_id'] ?? null;
    $local = $data['local'] ?? '';
    $data_inicio = $data['data_inicio'] ?? '';
    $data_fim = $data['data_fim'] ?? '';
    $documentos_necessarios = $data['documentos_necessarios'] ?? '';
    $instrucoes = $data['instrucoes'] ?? '';
    $ativo = $data['ativo'] ?? true;
    
    if (!$evento_id) {
        echo json_encode(['success' => false, 'error' => 'ID do evento é obrigatório']);
        exit;
    }
    
    if (empty($local) || empty($data_inicio) || empty($data_fim)) {
        echo json_encode(['success' => false, 'error' => 'Local, data de início e data de fim são obrigatórios']);
        exit;
    }
    
    // Converter data_inicio e data_fim para data_retirada, horario_inicio e horario_fim
    $data_retirada = date('Y-m-d', strtotime($data_inicio));
    $horario_inicio = date('H:i:s', strtotime($data_inicio));
    $horario_fim = date('H:i:s', strtotime($data_fim));
    
    // Verificar se o evento pertence ao organizador e não está excluído
    $sql_verificar = "SELECT id FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL";
    $stmt_verificar = $pdo->prepare($sql_verificar);
    $stmt_verificar->execute([$evento_id, $organizador_id, $usuario_id]);
    
    if (!$stmt_verificar->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Evento não encontrado, foi excluído ou sem permissão']);
        exit;
    }
    
    // Inserir novo local
    $sql = "INSERT INTO retirada_kits_evento 
                (evento_id, local_retirada, data_retirada, horario_inicio, horario_fim, documentos_necessarios, instrucoes_retirada, ativo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$evento_id, $local, $data_retirada, $horario_inicio, $horario_fim, $documentos_necessarios, $instrucoes, $ativo]);
    
    echo json_encode(['success' => true, 'message' => 'Local de retirada criado com sucesso']);
    
} catch (Exception $e) {
    http_response_code(500);
    error_log("Erro em create.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
?>
