<?php
/**
 * Endpoint HTTP para Cancelamento Automático de Inscrições Expiradas
 * 
 * Permite execução manual via requisição HTTP
 * Útil como fallback quando o CRON não está funcionando
 * 
 * Segurança: Pode ser protegido com token ou IP whitelist
 * 
 * Uso:
 * GET /api/cron/cancelar_inscricoes_expiradas_http.php
 * POST /api/cron/cancelar_inscricoes_expiradas_http.php?token=SEU_TOKEN
 */

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/cancelar_inscricoes_expiradas_helper.php';

header('Content-Type: application/json');

// ✅ SEGURANÇA: Verificar token (opcional, mas recomendado)
// Descomente e configure um token secreto para proteger este endpoint
$token_secreto = getenv('CANCELAR_INSCRICOES_TOKEN') ?: null;
$token_recebido = $_GET['token'] ?? $_POST['token'] ?? null;

if ($token_secreto && $token_recebido !== $token_secreto) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Token inválido. Acesso negado.',
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

// ✅ SEGURANÇA ALTERNATIVA: IP Whitelist (opcional)
// Descomente e configure IPs permitidos
/*
$ips_permitidos = ['127.0.0.1', '::1']; // Adicione IPs do servidor
$ip_atual = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($ip_atual, $ips_permitidos)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'IP não autorizado.',
        'ip' => $ip_atual,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}
*/

try {
    // Executar cancelamento usando helper function
    $resultado = cancelarInscricoesExpiradas($pdo, false);
    
    if (!$resultado['success']) {
        http_response_code(500);
    }
    
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar cancelamentos',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
