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
$inscricao_id = isset($body['inscricao_id']) ? (int) $body['inscricao_id'] : null;
$pagamento_ml_id = isset($body['pagamento_ml_id']) ? (int) $body['pagamento_ml_id'] : null;
$valor = $body['valor'] ?? null;
$status = isset($body['status']) ? (string) $body['status'] : 'aberto';
$motivo = isset($body['motivo']) ? (string) $body['motivo'] : null;
$prazo_resposta = isset($body['prazo_resposta']) ? (string) $body['prazo_resposta'] : null;
$evidencias_url = isset($body['evidencias_url']) ? (string) $body['evidencias_url'] : null;
$raw_payload = $body['raw_payload'] ?? null;

if ($evento_id <= 0 || $valor === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Campos obrigatorios: evento_id, valor',
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

try {
    $pdo->beginTransaction();

    $ret = fin_chargeback_registrar(
        $pdo,
        $evento_id,
        $inscricao_id,
        $pagamento_ml_id,
        $valor,
        $status,
        $motivo,
        $prazo_resposta,
        $evidencias_url,
        $raw_payload,
        $usuario_id
    );

    if (!($ret['success'] ?? false)) {
        $pdo->rollBack();
        http_response_code(422);
        echo json_encode($ret, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pdo->commit();
    echo json_encode($ret, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erro em chargeback_registrar.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao registrar chargeback'], JSON_UNESCAPED_UNICODE);
}
