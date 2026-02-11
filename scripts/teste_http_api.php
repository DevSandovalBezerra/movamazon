<?php

/**
 * Teste da API via HTTP (como seria chamada pela página web)
 */

echo "=== TESTE DA API VIA HTTP ===\n\n";

// Simular sessão
session_start();
$_SESSION['user_id'] = 4;
$_SESSION['user_name'] = 'Daniel Dias Filho';
$_SESSION['user_email'] = 'daniel@gmail.com';
$_SESSION['papel'] = 'participante';

echo "1. ✅ Sessão configurada\n";
echo "   - User ID: " . $_SESSION['user_id'] . "\n\n";

$base_url = 'http://localhost/movamazonas';
$api_url = $base_url . '/api/participante/get_inscricao.php?inscricao_id=1';

echo "2. 🔗 Testando via HTTP:\n";
echo "   - URL: $api_url\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "3. 📋 Resultado:\n";
echo "   - HTTP Code: $http_code\n";

if ($error) {
    echo "   - Erro cURL: $error\n";
} else {
    echo "   - Resposta: $result\n\n";

    $data = json_decode($result, true);
    if ($data) {
        echo "4. ✅ JSON válido:\n";
        echo "   - Success: " . ($data['success'] ? 'true' : 'false') . "\n";

        if ($data['success']) {
            echo "   - ✅ API funcionando via HTTP!\n";
            echo "   - ID: " . $data['inscricao']['id'] . "\n";
            echo "   - Status: " . $data['inscricao']['status'] . "\n";
            echo "   - Valor: R$ " . number_format($data['inscricao']['valor_total'], 2, ',', '.') . "\n";
        } else {
            echo "   - ❌ Erro: " . $data['message'] . "\n";
        }
    } else {
        echo "4. ❌ JSON inválido\n";
    }
}

echo "\n=== FIM DO TESTE HTTP ===\n";
