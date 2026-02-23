<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/organizador_context.php';
require_once __DIR__ . '/financeiro_service.php';

verificarAutenticacao('organizador');
$ctx = requireOrganizadorContext($pdo);

$evento_id = isset($_GET['evento_id']) ? (int) $_GET['evento_id'] : 0;
if ($evento_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'evento_id e obrigatorio'], JSON_UNESCAPED_UNICODE);
    exit;
}

$usuario_id = (int) $ctx['usuario_id'];
$organizador_id = (int) $ctx['organizador_id'];

if (!fin_evento_pertence_organizador($pdo, $evento_id, $organizador_id, $usuario_id)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Evento nao pertence ao organizador autenticado'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $data = fin_visao_geral($pdo, $evento_id);
    echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log('Erro ao carregar visao geral financeira: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao carregar visao geral'], JSON_UNESCAPED_UNICODE);
}
