<?php
session_start();
header('Content-Type: application/json');

// Verificar autenticação
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit();
}

require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || !isset($input['ativo'])) {
        throw new Exception('ID do lote e status são obrigatórios');
    }

    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];

    $lote_id = (int)$input['id'];
    $ativo = (bool)$input['ativo'];

    // Validar se o lote existe e pertence ao organizador (novo + legado)
    $stmt = $pdo->prepare("
        SELECT li.id, li.numero_lote, e.nome as evento_nome 
        FROM lotes_inscricao li
        INNER JOIN eventos e ON li.evento_id = e.id
        WHERE li.id = ? AND (e.organizador_id = ? OR e.organizador_id = ?)
    ");
    $stmt->execute([$lote_id, $organizador_id, $usuario_id]);
    $lote = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lote) {
        throw new Exception('Lote não encontrado ou não autorizado');
    }

    // Atualizar status do lote
    $stmt = $pdo->prepare("UPDATE lotes_inscricao SET ativo = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$ativo ? 1 : 0, $lote_id]);

    $acao = $ativo ? 'ativado' : 'desativado';
    error_log("✅ Lote ID: $lote_id $acao - Evento: {$lote['evento_nome']} - Lote: {$lote['numero_lote']}");

    echo json_encode([
        'success' => true,
        'message' => "Lote $acao com sucesso"
    ]);

} catch (Exception $e) {
    error_log("💥 Erro ao alterar status do lote: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 
