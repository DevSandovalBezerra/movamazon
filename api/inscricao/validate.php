<?php
require_once '../db.php';
require_once '../helpers/inscricao_logger.php';
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$evento_id = isset($data['evento_id']) ? (int)$data['evento_id'] : 0;
$cupom = isset($data['cupom']) ? trim($data['cupom']) : '';
if (!$evento_id || !$cupom) {
    logInscricaoPagamento('WARNING', 'VALIDACAO_FALHOU', [
        'evento_id' => $evento_id,
        'campo_faltante' => !$evento_id ? 'evento_id' : 'cupom'
    ]);
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Evento ou cupom não informado']);
    exit;
}
$stmt = $pdo->prepare("SELECT id, codigo, valor_desconto, usos_atuais, max_uso FROM cupons_remessa WHERE evento_id = ? AND codigo = ? AND ativo = 1");
$stmt->execute([$evento_id, $cupom]);
$cupom_data = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$cupom_data) {
    logInscricaoPagamento('WARNING', 'CUPOM_INVALIDO', [
        'evento_id' => $evento_id,
        'cupom' => substr($cupom, 0, 3) . '***'
    ]);
    echo json_encode(['success' => false, 'error' => 'Cupom inválido']);
    exit;
}
if ($cupom_data['usos_atuais'] >= $cupom_data['max_uso']) {
    logInscricaoPagamento('WARNING', 'CUPOM_ESGOTADO', [
        'evento_id' => $evento_id,
        'cupom_id' => $cupom_data['id'],
        'usos_atuais' => $cupom_data['usos_atuais'],
        'max_uso' => $cupom_data['max_uso']
    ]);
    echo json_encode(['success' => false, 'error' => 'Cupom esgotado']);
    exit;
}
logInscricaoPagamento('INFO', 'VALIDACAO_CUPOM_SUCESSO', [
    'evento_id' => $evento_id,
    'cupom_id' => $cupom_data['id'],
    'valor_desconto' => $cupom_data['valor_desconto']
]);
echo json_encode(['success' => true, 'valor_desconto' => $cupom_data['valor_desconto']]);
