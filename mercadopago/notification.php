<?php  

header('Content-Type: application/json');

/**
 * Endpoint legado de notificação.
 *
 * O endpoint oficial de webhook deste projeto é:
 *   /api/mercadolivre/webhook.php
 *
 * Este arquivo é mantido apenas para compatibilidade.
 * Caso ainda haja alguma configuração antiga apontando para notification.php
 * no painel do Mercado Pago, o acesso será logado para facilitar a correção.
 */

$log_file = dirname(__DIR__, 2) . '/logs/webhook_mp.log';
$body_raw = file_get_contents('php://input');

// Garantir diretório de logs
$log_dir = dirname($log_file);
if (!is_dir($log_dir)) {
    @mkdir($log_dir, 0755, true);
}

file_put_contents(
    $log_file,
    date('Y-m-d H:i:s') . " [notification.php] AVISO: endpoint legado chamado. Configure o webhook para /api/mercadolivre/webhook.php. Payload: " . $body_raw . "\n",
    FILE_APPEND
);

echo json_encode([
    'ok' => true,
    'message' => 'Endpoint legado. Use /api/mercadolivre/webhook.php como webhook oficial.'
]);
