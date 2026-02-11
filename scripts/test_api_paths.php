<?php

/**
 * Script para testar os caminhos das APIs
 */

echo "=== TESTE DE CAMINHOS DAS APIs ===\n\n";

// Simular estrutura de pastas
$base_path = __DIR__;
echo "Base path: $base_path\n";

// Testar caminhos relativos
$test_paths = [
    'frontend/paginas/participante/index.php' => [
        'api/participante/get_inscricoes.php' => '../../../api/participante/get_inscricoes.php',
        'api/participante/get_inscricao.php' => '../../../api/participante/get_inscricao.php',
        'api/mercadolivre/create_payment.php' => '../../../api/mercadolivre/create_payment.php'
    ]
];

foreach ($test_paths as $page => $apis) {
    echo "\n--- Testando página: $page ---\n";

    foreach ($apis as $api => $relative_path) {
        $full_path = $base_path . '/' . $relative_path;
        $exists = file_exists($full_path);

        echo "API: $api\n";
        echo "Caminho relativo: $relative_path\n";
        echo "Caminho completo: $full_path\n";
        echo "Existe: " . ($exists ? "✅ SIM" : "❌ NÃO") . "\n\n";
    }
}

// Testar URLs
echo "--- Testando URLs ---\n";
$base_url = 'http://localhost/movamazonas';
$test_urls = [
    'get_inscricoes' => $base_url . '/api/participante/get_inscricoes.php',
    'get_inscricao' => $base_url . '/api/participante/get_inscricao.php?inscricao_id=1',
    'create_payment' => $base_url . '/api/mercadolivre/create_payment.php'
];

foreach ($test_urls as $name => $url) {
    echo "Testando: $name\n";
    echo "URL: $url\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true); // Só verificar se existe
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "Erro cURL: $error\n";
    } else {
        echo "HTTP Code: $http_code\n";
        echo "Status: " . ($http_code < 400 ? "✅ OK" : "❌ ERRO") . "\n";
    }
    echo "\n";
}

echo "=== FIM DO TESTE ===\n";
