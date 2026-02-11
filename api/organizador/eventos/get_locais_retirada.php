<?php
session_start();
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$ctx = requireOrganizadorContext($pdo);
$usuario_id = $ctx['usuario_id'];
$organizador_id = $ctx['organizador_id'];
$evento_id = $_GET['evento_id'] ?? null;

if (!$evento_id) {
    echo json_encode(['success' => false, 'message' => 'ID do evento é obrigatório']);
    exit;
}

try {
    // Verificar se o evento pertence ao organizador
    $stmt = $pdo->prepare("SELECT id FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL");
    $stmt->execute([$evento_id, $organizador_id, $usuario_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Evento não encontrado ou não autorizado']);
        exit;
    }
    
    // Buscar locais de retirada
    $stmt = $pdo->prepare("
        SELECT 
            id,
            data_retirada,
            horario_inicio,
            horario_fim,
            local_retirada,
            endereco_completo,
            instrucoes_retirada,
            retirada_terceiros,
            documentos_necessarios,
            ativo,
            data_criacao
        FROM retirada_kits_evento 
        WHERE evento_id = ? AND ativo = 1
        ORDER BY data_retirada ASC, horario_inicio ASC
    ");
    
    $stmt->execute([$evento_id]);
    $locais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'locais' => $locais
    ]);
    
} catch (PDOException $e) {
    error_log('Erro ao buscar locais de retirada: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
