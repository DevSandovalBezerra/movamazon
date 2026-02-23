<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/organizador_context.php';
require_once __DIR__ . '/financeiro_service.php';

verificarAutenticacao('organizador');
$ctx = requireOrganizadorContext($pdo);

$body = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) {
    $body = [];
}

$evento_id = isset($body['evento_id']) ? (int) $body['evento_id'] : 0;
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

$ret = fin_fechamento_gerar($pdo, $evento_id, $usuario_id);
if (!$ret['success']) {
    http_response_code(422);
}

echo json_encode($ret, JSON_UNESCAPED_UNICODE);
