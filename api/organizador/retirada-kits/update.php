<?php
header('Content-Type: application/json');
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
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
    
    $local_id = $data['id'] ?? null;
    $evento_id = $data['evento_id'] ?? null;
    
    if (!$local_id) {
        echo json_encode(['success' => false, 'error' => 'ID do local é obrigatório']);
        exit;
    }
    
    if (!$evento_id) {
        echo json_encode(['success' => false, 'error' => 'ID do evento é obrigatório']);
        exit;
    }
    
    $local = $data['local'] ?? '';
    $data_inicio = $data['data_inicio'] ?? '';
    $data_fim = $data['data_fim'] ?? '';
    $documentos_necessarios = $data['documentos_necessarios'] ?? '';
    $instrucoes = $data['instrucoes'] ?? '';
    $ativo = isset($data['ativo']) ? (bool)$data['ativo'] : true;
    
    if (empty($local) || empty($data_inicio) || empty($data_fim)) {
        echo json_encode(['success' => false, 'error' => 'Local, data de início e data de fim são obrigatórios']);
        exit;
    }

    // Verificar se o local pertence ao organizador
    $sql_verificar = "SELECT rk.id 
                      FROM retirada_kits_evento rk 
                      INNER JOIN eventos e ON rk.evento_id = e.id 
                      WHERE rk.id = ? 
                        AND (e.organizador_id = ? OR e.organizador_id = ?) 
                        AND e.deleted_at IS NULL";
    $stmt_verificar = $pdo->prepare($sql_verificar);
    $stmt_verificar->execute([$local_id, $organizador_id, $usuario_id]);
    
    if (!$stmt_verificar->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Local não encontrado ou sem permissão']);
        exit;
    }
    
    // Converter data_inicio e data_fim para data_retirada, horario_inicio e horario_fim
    $data_retirada = date('Y-m-d', strtotime($data_inicio));
    $horario_inicio = date('H:i:s', strtotime($data_inicio));
    $horario_fim = date('H:i:s', strtotime($data_fim));
    
    // Atualizar local específico por ID
    $sql = "UPDATE retirada_kits_evento SET 
                local_retirada = ?, 
                data_retirada = ?, 
                horario_inicio = ?, 
                horario_fim = ?, 
                documentos_necessarios = ?, 
                instrucoes_retirada = ?, 
                ativo = ?
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $local, 
        $data_retirada, 
        $horario_inicio, 
        $horario_fim, 
        $documentos_necessarios, 
        $instrucoes, 
        $ativo ? 1 : 0,
        $local_id
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Local de retirada atualizado com sucesso']);
    
} catch (Exception $e) {
    http_response_code(500);
    error_log("Erro em update.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
?>
