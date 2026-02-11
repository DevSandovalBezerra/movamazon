<?php
// ✅ Garantir que não há output antes dos headers
ob_start();

// ✅ Verificar se sessão já foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';

// ✅ Limpar qualquer output acidental antes de enviar headers
ob_clean();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // ✅ Incluir config.php apenas uma vez
    $config = require __DIR__ . '/config.php';

    // ✅ Validar que config foi carregado corretamente
    if (!is_array($config) || !isset($config['public_key'])) {
        throw new Exception('Configuração do Mercado Pago não encontrada');
    }

    // ✅ Retornar public key
    echo json_encode([
        'success' => true,
        'public_key' => $config['public_key'] ?? '',
        'environment' => 'production',
        'is_production' => true,
        'has_valid_tokens' => $config['has_valid_tokens'] ?? false
    ]);
} catch (Exception $e) {
    // ✅ Log do erro para debug
    error_log("Erro em get_public_key.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao obter configuração do Mercado Pago',
        'error' => $e->getMessage()
    ]);
}