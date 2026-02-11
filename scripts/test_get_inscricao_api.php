<?php

/**
 * Script para testar a API get_inscricao.php
 */

echo "=== TESTE DA API get_inscricao.php ===\n\n";

// Simular sessão de usuário
session_start();
$_SESSION['user_id'] = 4; // ID do usuário Daniel
$_SESSION['user_name'] = 'Daniel Dias Filho';
$_SESSION['user_email'] = 'daniel@gmail.com';

echo "1. Sessão simulada:\n";
echo "   - User ID: " . $_SESSION['user_id'] . "\n";
echo "   - User Name: " . $_SESSION['user_name'] . "\n";
echo "   - User Email: " . $_SESSION['user_email'] . "\n\n";

// Testar API diretamente
echo "2. Testando API diretamente:\n";

// Simular chamada GET
$_GET['inscricao_id'] = 1;

// Capturar output
ob_start();
include 'api/participante/get_inscricao.php';
$output = ob_get_clean();

echo "   - Resposta da API:\n";
echo $output . "\n";

// Tentar decodificar JSON
$data = json_decode($output, true);
if ($data) {
    echo "   - JSON válido: ✅\n";
    echo "   - Success: " . ($data['success'] ? 'true' : 'false') . "\n";
    if (isset($data['inscricao'])) {
        echo "   - Inscrição encontrada: ✅\n";
        echo "   - ID: " . $data['inscricao']['id'] . "\n";
        echo "   - Status: " . $data['inscricao']['status'] . "\n";
        echo "   - Valor Total: R$ " . number_format($data['inscricao']['valor_total'], 2, ',', '.') . "\n";
    }
} else {
    echo "   - JSON inválido: ❌\n";
    echo "   - Erro: " . json_last_error_msg() . "\n";
}

echo "\n3. Testando via cURL:\n";

$url = 'http://localhost/movamazonas/api/participante/get_inscricao.php?inscricao_id=1';
echo "   - URL: $url\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "   - Erro cURL: $error\n";
} else {
    echo "   - HTTP Code: $http_code\n";
    echo "   - Resposta: " . substr($result, 0, 200) . "...\n";

    $data = json_decode($result, true);
    if ($data && $data['success']) {
        echo "   - Status: ✅ SUCESSO\n";
    } else {
        echo "   - Status: ❌ ERRO\n";
    }
}

echo "\n=== FIM DO TESTE ===\n";
