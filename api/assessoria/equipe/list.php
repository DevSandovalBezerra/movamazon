<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../middleware.php';
requireAssessorAPI();

$assessoria_id = getAssessoriaDoUsuario();
if (!$assessoria_id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Assessoria nao encontrada']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT ae.id, ae.funcao, ae.status, ae.created_at,
               u.id as usuario_id, u.nome_completo, u.email, u.telefone
        FROM assessoria_equipe ae
        JOIN usuarios u ON ae.usuario_id = u.id
        WHERE ae.assessoria_id = ?
        ORDER BY ae.funcao ASC, u.nome_completo ASC
    ");
    $stmt->execute([$assessoria_id]);
    $membros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'membros' => $membros
    ]);
} catch (Exception $e) {
    error_log("[ASSESSORIA_EQUIPE_LIST] Erro: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno']);
}
