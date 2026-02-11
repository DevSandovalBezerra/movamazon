<?php

/**
 * Script de teste para verificar se a API get_inscricao.php está funcionando
 */

// Simular sessão de usuário para teste
session_start();
$_SESSION['user_id'] = 1; // ID de teste

echo "=== TESTE DA API get_inscricao.php ===\n\n";

// Testar com inscrição ID 1
$inscricao_id = 1;
$url = "http://localhost/movamazonas/api/participante/get_inscricao.php?inscricao_id=" . $inscricao_id;

echo "1. Testando URL: $url\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "2. HTTP Code: $http_code\n";
echo "3. Response: $response\n";

if ($error) {
    echo "4. cURL Error: $error\n";
}

if ($http_code === 200) {
    $data = json_decode($response, true);
    if ($data && isset($data['success']) && $data['success']) {
        echo "✅ API funcionando corretamente!\n";
        echo "✅ Inscrição encontrada: " . $data['inscricao']['evento']['nome'] . "\n";
        echo "✅ Valor total: R$ " . number_format($data['inscricao']['valor_total'], 2, ',', '.') . "\n";
    } else {
        echo "❌ API retornou erro: " . ($data['message'] ?? 'Erro desconhecido') . "\n";
    }
} else {
    echo "❌ Erro HTTP: $http_code\n";
}

echo "\n=== FIM DO TESTE ===\n";
