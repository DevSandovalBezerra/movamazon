<?php
/**
 * 🧪 SCRIPT DE TESTE DO WEBHOOK
 * 
 * Simula uma notificação do Mercado Pago para testar o webhook localmente
 * 
 * USO:
 * 1. Substitua PAYMENT_ID_REAL por um ID de pagamento existente
 * 2. Execute: php api/diagnostico/testar_webhook.php
 * 3. OU acesse via navegador: https://www.movamazon.com.br/api/diagnostico/testar_webhook.php
 */

// ========================================
// 🔧 CONFIGURAÇÃO DO TESTE
// ========================================

// ❗ IMPORTANTE: Substitua com um Payment ID real do Mercado Pago
$PAYMENT_ID_TESTE = '1234567890'; // ⚠️ ALTERE AQUI!

// URL do webhook (produção)
$WEBHOOK_URL = 'https://www.movamazon.com.br/api/mercadolivre/webhook.php';

// ========================================
// 📤 SIMULAÇÃO DA NOTIFICAÇÃO
// ========================================

// Payload que o Mercado Pago envia
$payload = [
    'action' => 'payment.updated',
    'api_version' => 'v1',
    'data' => [
        'id' => $PAYMENT_ID_TESTE
    ],
    'date_created' => date('c'),
    'id' => rand(100000000, 999999999),
    'live_mode' => true,
    'type' => 'payment',
    'user_id' => '123456789'
];

echo "🧪 TESTE DE WEBHOOK - MERCADO PAGO\n";
echo "=" . str_repeat("=", 50) . "\n\n";
echo "📍 URL do Webhook: $WEBHOOK_URL\n";
echo "💳 Payment ID: $PAYMENT_ID_TESTE\n\n";

if ($PAYMENT_ID_TESTE === '1234567890') {
    echo "❌ ERRO: Você precisa alterar o PAYMENT_ID_TESTE!\n\n";
    echo "📝 INSTRUÇÕES:\n";
    echo "1. Faça um pagamento de teste no sistema\n";
    echo "2. Pegue o Payment ID nos logs do Mercado Pago\n";
    echo "3. Altere a variável \$PAYMENT_ID_TESTE neste arquivo\n";
    echo "4. Execute novamente\n\n";
    exit(1);
}

echo "📦 Payload:\n";
echo json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

echo "📡 Enviando requisição...\n";

// ========================================
// 🚀 ENVIAR REQUISIÇÃO
// ========================================

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $WEBHOOK_URL,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'User-Agent: MercadoPago Webhook Test'
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_VERBOSE => false
]);

$start = microtime(true);
$response = curl_exec($ch);
$elapsed = round((microtime(true) - $start) * 1000, 2);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 RESULTADO\n";
echo str_repeat("=", 50) . "\n\n";

// ========================================
// 📈 ANÁLISE DO RESULTADO
// ========================================

echo "⏱️  Tempo de Resposta: {$elapsed}ms ";
if ($elapsed < 500) {
    echo "✅ EXCELENTE (< 500ms)\n";
} elseif ($elapsed < 1000) {
    echo "⚠️  ACEITÁVEL (< 1s)\n";
} else {
    echo "❌ MUITO LENTO (> 1s)\n";
}

echo "🌐 HTTP Status: $http_code ";
if ($http_code === 200) {
    echo "✅ OK\n";
} else {
    echo "❌ ERRO\n";
}

if ($curl_error) {
    echo "❌ Erro cURL: $curl_error\n";
}

echo "\n📄 Resposta do Servidor:\n";
echo str_repeat("-", 50) . "\n";
echo $response . "\n";
echo str_repeat("-", 50) . "\n\n";

// ========================================
// 📋 VERIFICAÇÕES
// ========================================

echo "🔍 VERIFICAÇÕES:\n\n";

$response_data = json_decode($response, true);

if ($response_data) {
    if (isset($response_data['status']) && $response_data['status'] === 'ok') {
        echo "✅ Webhook respondeu corretamente\n";
    } else {
        echo "⚠️  Resposta inesperada\n";
    }
    
    if (isset($response_data['queued']) && $response_data['queued'] === true) {
        echo "✅ Pagamento adicionado à fila\n";
    }
    
    if (isset($response_data['response_time_ms'])) {
        echo "✅ Tempo de resposta HTTP: " . $response_data['response_time_ms'] . "ms\n";
    }
} else {
    echo "⚠️  Resposta não é JSON válido\n";
}

echo "\n📝 PRÓXIMOS PASSOS:\n";
echo "1. Verifique o arquivo logs/webhook_mp.log\n";
echo "2. Verifique o arquivo logs/inscricoes_pagamentos.log\n";
echo "3. Confira no banco se o pagamento foi atualizado\n";
echo "4. Verifique se o email foi enviado (se pago)\n\n";

// ========================================
// 📊 VERIFICAR LOGS
// ========================================

$base_path = dirname(dirname(__DIR__));
$log_webhook = $base_path . '/logs/webhook_mp.log';
$log_inscricoes = $base_path . '/logs/inscricoes_pagamentos.log';

if (file_exists($log_webhook)) {
    echo "📂 Últimas linhas do webhook_mp.log:\n";
    echo str_repeat("-", 50) . "\n";
    $lines = file($log_webhook);
    $last_lines = array_slice($lines, -10);
    foreach ($last_lines as $line) {
        echo $line;
    }
    echo str_repeat("-", 50) . "\n\n";
}

echo "✅ TESTE CONCLUÍDO!\n\n";
