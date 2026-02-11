<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/inscricao_logger.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    logInscricaoPagamento('WARNING', 'DADOS_INVALIDOS_PROCESS_PAYMENT', []);
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
    exit;
}

// Log início do processamento
logInscricaoPagamento('INFO', 'INICIO_PROCESSAMENTO_PAGAMENTO', [
    'inscricao_id' => $data['inscricao_id'] ?? null
]);

// Simulação de processamento de pagamento
sleep(1);

logInscricaoPagamento('SUCCESS', 'PAGAMENTO_PROCESSADO_SIMULACAO', [
    'inscricao_id' => $data['inscricao_id'] ?? null,
    'mensagem' => 'Pagamento processado (simulação)'
]);

echo json_encode(['success' => true, 'mensagem' => 'Pagamento processado (simulação). Integração Mercado Livre será implementada.']);
