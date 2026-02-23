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
$beneficiario_id = isset($body['beneficiario_id']) ? (int) $body['beneficiario_id'] : 0;
$conta_id = isset($body['conta_bancaria_id']) ? (int) $body['conta_bancaria_id'] : 0;
$valor_in = $body['valor'] ?? null;
$agendar_para = isset($body['agendado_para']) ? (string) $body['agendado_para'] : '';

if ($evento_id <= 0 || $beneficiario_id <= 0 || $conta_id <= 0 || $valor_in === null || $agendar_para === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Campos obrigatorios: evento_id, beneficiario_id, conta_bancaria_id, valor, agendado_para',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$usuario_id = (int) $ctx['usuario_id'];
$organizador_id = (int) $ctx['organizador_id'];

if (!fin_evento_pertence_organizador($pdo, $evento_id, $organizador_id, $usuario_id)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Evento nao pertence ao organizador autenticado'], JSON_UNESCAPED_UNICODE);
    exit;
}

$ret = fin_repasse_agendar(
    $pdo,
    $evento_id,
    $beneficiario_id,
    $conta_id,
    $valor_in,
    $agendar_para,
    $usuario_id
);

if (!$ret['success']) {
    http_response_code(422);
}

echo json_encode($ret, JSON_UNESCAPED_UNICODE);
