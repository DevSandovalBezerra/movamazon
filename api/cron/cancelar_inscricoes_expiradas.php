<?php
/**
 * Rotina de Cancelamento Automático de Inscrições Expiradas
 * 
 * Deve ser executada via cron job a cada hora ou diariamente
 * Exemplo cron: 0 * * * * /usr/bin/php /caminho/para/api/cron/cancelar_inscricoes_expiradas.php
 * 
 * Regras de cancelamento:
 * 1. Boletos expirados: data_expiracao_pagamento < NOW()
 * 2. Pendentes por mais de 72 horas: data_inscricao < NOW() - 72 HOURS
 * 3. Após data de encerramento: data_inscricao > evento.data_fim_inscricoes
 */

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/cancelar_inscricoes_expiradas_helper.php';

header('Content-Type: application/json');

// ✅ REGISTRAR EXECUÇÃO DO CRON (para verificação)
$log_execucao_file = __DIR__ . '/../../logs/cron_execucoes.log';
$log_dir = dirname($log_execucao_file);
if (!is_dir($log_dir)) {
    @mkdir($log_dir, 0755, true);
}

// Detectar se é execução automática (CRON) ou manual
$eh_cron = (
    php_sapi_name() === 'cli' ||
    !isset($_SERVER['REQUEST_METHOD']) ||
    empty($_SERVER['REQUEST_METHOD']) ||
    (!isset($_SERVER['HTTP_USER_AGENT']) && !isset($_SERVER['REMOTE_ADDR']))
);

$execucao_info = [
    'timestamp' => date('Y-m-d H:i:s'),
    'tipo' => $eh_cron ? 'CRON_AUTOMATICO' : 'MANUAL',
    'sapi' => php_sapi_name(),
    'usuario' => get_current_user(),
    'pid' => getmypid(),
    'server_name' => $_SERVER['SERVER_NAME'] ?? 'CLI',
    'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'CRON',
    'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'localhost',
    'script_name' => $_SERVER['SCRIPT_NAME'] ?? __FILE__
];

@file_put_contents(
    $log_execucao_file, 
    json_encode($execucao_info) . "\n", 
    FILE_APPEND | LOCK_EX
);

// Usar helper function reutilizável
$resultado = cancelarInscricoesExpiradas($pdo, false);

// Adicionar info de execução ao resultado
$resultado['execucao_info'] = $execucao_info;

if (!$resultado['success']) {
    http_response_code(500);
}

echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
