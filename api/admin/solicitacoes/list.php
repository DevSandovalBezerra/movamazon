<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth_middleware.php';
require_once __DIR__ . '/../../db.php';

header('Content-Type: application/json');

if (!requererAdmin(false)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$allowedStatus = ['novo', 'em_analise', 'aprovado', 'recusado'];

$params = [];
$where = '';
if ($status !== '' && in_array($status, $allowedStatus, true)) {
    $where = 'WHERE s.status = :status';
    $params['status'] = $status;
}

$sql = "
    SELECT s.*, DATE_FORMAT(s.criado_em, '%d/%m/%Y %H:%i') AS criado_em_formatado
    FROM solicitacoes_evento s
    $where
    ORDER BY s.criado_em DESC
    LIMIT 200
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $solicitacoes]);
} catch (Throwable $e) {
    error_log('[ADMIN_SOLICITACOES_LIST] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao listar solicitações']);
}

