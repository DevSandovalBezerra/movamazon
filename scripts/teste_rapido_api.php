<?php

/**
 * Teste rápido da API após correção
 */

// Simular sessão
session_start();
$_SESSION['user_id'] = 4;
$_SESSION['user_name'] = 'Daniel Dias Filho';
$_SESSION['user_email'] = 'daniel@gmail.com';
$_SESSION['papel'] = 'participante';

echo "=== TESTE RÁPIDO APÓS CORREÇÃO ===\n\n";

echo "1. ✅ Sessão configurada:\n";
echo "   - User ID: " . $_SESSION['user_id'] . "\n\n";

// Simular parâmetro GET
$_GET['inscricao_id'] = 1;

echo "2. ✅ Testando API:\n";

// Capturar output
ob_start();
include __DIR__ . '/../api/participante/get_inscricao.php';
$output = ob_get_clean();

echo "3. 📋 Resposta:\n";
echo $output . "\n\n";

// Tentar decodificar JSON
$data = json_decode($output, true);
if ($data) {
    echo "4. ✅ JSON válido:\n";
    echo "   - Success: " . ($data['success'] ? 'true' : 'false') . "\n";

    if ($data['success']) {
        echo "   - ✅ API funcionando!\n";
        echo "   - ID: " . $data['inscricao']['id'] . "\n";
        echo "   - Status: " . $data['inscricao']['status'] . "\n";
        echo "   - Valor: R$ " . number_format($data['inscricao']['valor_total'], 2, ',', '.') . "\n";
    } else {
        echo "   - ❌ Erro: " . $data['message'] . "\n";
    }
} else {
    echo "4. ❌ JSON inválido\n";
}

echo "\n=== FIM DO TESTE ===\n";
