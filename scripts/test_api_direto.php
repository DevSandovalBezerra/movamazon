<?php

/**
 * Teste direto da API get_inscricao.php
 */

// Simular sessão
session_start();
$_SESSION['user_id'] = 4;
$_SESSION['user_name'] = 'Daniel Dias Filho';
$_SESSION['user_email'] = 'daniel@gmail.com';
$_SESSION['papel'] = 'participante';

echo "=== TESTE DIRETO DA API get_inscricao.php ===\n\n";

echo "1. Sessão configurada:\n";
echo "   - User ID: " . ($_SESSION['user_id'] ?? 'NÃO DEFINIDO') . "\n";
echo "   - User Name: " . ($_SESSION['user_name'] ?? 'NÃO DEFINIDO') . "\n\n";

// Simular parâmetro GET
$_GET['inscricao_id'] = 1;

echo "2. Testando API diretamente:\n";
echo "   - inscricao_id: " . $_GET['inscricao_id'] . "\n\n";

// Capturar output
ob_start();
include __DIR__ . '/../api/participante/get_inscricao.php';
$output = ob_get_clean();

echo "3. Resposta da API:\n";
echo $output . "\n\n";

// Tentar decodificar JSON
$data = json_decode($output, true);
if ($data) {
    echo "4. JSON válido: ✅\n";
    echo "   - Success: " . ($data['success'] ? 'true' : 'false') . "\n";

    if (isset($data['inscricao'])) {
        echo "   - Inscrição encontrada: ✅\n";
        echo "   - ID: " . $data['inscricao']['id'] . "\n";
        echo "   - Status: " . $data['inscricao']['status'] . "\n";
        echo "   - Valor Total: R$ " . number_format($data['inscricao']['valor_total'], 2, ',', '.') . "\n";
    } else {
        echo "   - Inscrição não encontrada: ❌\n";
    }

    if (isset($data['message'])) {
        echo "   - Mensagem: " . $data['message'] . "\n";
    }
} else {
    echo "4. JSON inválido: ❌\n";
    echo "   - Erro: " . json_last_error_msg() . "\n";
}

echo "\n=== FIM DO TESTE ===\n";
