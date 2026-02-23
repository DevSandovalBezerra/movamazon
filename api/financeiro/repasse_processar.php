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

$repasse_id = isset($body['repasse_id']) ? (int) $body['repasse_id'] : 0;
$status = isset($body['status']) ? (string) $body['status'] : '';
$comprovante_url = isset($body['comprovante_url']) ? (string) $body['comprovante_url'] : null;
$gateway_transfer_id = isset($body['gateway_transfer_id']) ? (string) $body['gateway_transfer_id'] : null;
$motivo_falha = isset($body['motivo_falha']) ? (string) $body['motivo_falha'] : null;

if ($repasse_id <= 0 || $status === '') {
    echo json_encode(['success' => false, 'message' => 'Campos obrigatorios: repasse_id, status'], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $pdo->prepare('SELECT id, evento_id FROM financeiro_repasses WHERE id = ? LIMIT 1');
$stmt->execute([$repasse_id]);
$repasse = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$repasse) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Repasse nao encontrado'], JSON_UNESCAPED_UNICODE);
    exit;
}

$evento_id = (int) $repasse['evento_id'];
$usuario_id = (int) $ctx['usuario_id'];
$organizador_id = (int) $ctx['organizador_id'];

if (!fin_evento_pertence_organizador($pdo, $evento_id, $organizador_id, $usuario_id)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Evento nao pertence ao organizador autenticado'], JSON_UNESCAPED_UNICODE);
    exit;
}

$ret = fin_repasse_processar(
    $pdo,
    $repasse_id,
    $status,
    $usuario_id,
    $comprovante_url,
    $gateway_transfer_id,
    $motivo_falha
);

if (!$ret['success']) {
    http_response_code(422);
}

echo json_encode($ret, JSON_UNESCAPED_UNICODE);
