<?php

/**
 * Script automatizado para testar funcionalidade de pagamento
 */

echo "=== TESTE AUTOMATIZADO - PAGAMENTO DE INSCRIÇÃO ===\n\n";

// Simular sessão
session_start();
$_SESSION['user_id'] = 4;
$_SESSION['user_name'] = 'Daniel Dias Filho';
$_SESSION['user_email'] = 'daniel@gmail.com';
$_SESSION['papel'] = 'participante';

echo "1. ✅ Sessão simulada configurada\n";
echo "   - User ID: " . $_SESSION['user_id'] . "\n";
echo "   - User Name: " . $_SESSION['user_name'] . "\n\n";

$base_url = 'http://localhost/movamazonas';
$test_results = [];

// Teste 1: Página de teste
echo "2. Testando página de teste:\n";
$test_url = $base_url . '/frontend/paginas/participante/index.php?page=teste-pagamento';
echo "   - URL: $test_url\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $test_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   - HTTP Code: $http_code\n";

$has_test_title = strpos($result, 'Teste - Pagamento de Inscrição') !== false;
$has_api_tests = strpos($result, 'Teste 1: APIs') !== false;
$has_interface_tests = strpos($result, 'Teste 2: Interface') !== false;
$has_flow_tests = strpos($result, 'Teste 3: Fluxo Completo') !== false;

echo "   - Título presente: " . ($has_test_title ? "✅ SIM" : "❌ NÃO") . "\n";
echo "   - Testes de API: " . ($has_api_tests ? "✅ SIM" : "❌ NÃO") . "\n";
echo "   - Testes de Interface: " . ($has_interface_tests ? "✅ SIM" : "❌ NÃO") . "\n";
echo "   - Testes de Fluxo: " . ($has_flow_tests ? "✅ SIM" : "❌ NÃO") . "\n";

$test_results['pagina_teste'] = $http_code < 400 && $has_test_title;

// Teste 2: APIs
echo "\n3. Testando APIs:\n";

$apis = [
    'get_inscricoes' => $base_url . '/api/participante/get_inscricoes.php',
    'get_inscricao' => $base_url . '/api/participante/get_inscricao.php?inscricao_id=1',
    'create_payment' => $base_url . '/api/mercadolivre/create_payment.php'
];

foreach ($apis as $name => $url) {
    echo "   - $name: $url\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    if ($name === 'create_payment') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'inscricao_id' => 1,
            'modalidade_nome' => 'Teste',
            'valor_total' => 100.00,
            'evento_nome' => 'Evento Teste'
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }

    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "     HTTP Code: $http_code\n";

    $data = json_decode($result, true);
    $is_success = $data && isset($data['success']) && $data['success'];

    echo "     Status: " . ($is_success ? "✅ SUCESSO" : "❌ ERRO") . "\n";

    $test_results["api_$name"] = $is_success;
}

// Teste 3: Páginas do fluxo
echo "\n4. Testando páginas do fluxo:\n";

$pages = [
    'minhas-inscricoes' => $base_url . '/frontend/paginas/participante/index.php?page=minhas-inscricoes',
    'pagamento-inscricao' => $base_url . '/frontend/paginas/participante/index.php?page=pagamento-inscricao&inscricao_id=1',
    'pagamento-sucesso' => $base_url . '/frontend/paginas/participante/index.php?page=pagamento-sucesso',
    'pagamento-pendente' => $base_url . '/frontend/paginas/participante/index.php?page=pagamento-pendente',
    'pagamento-erro' => $base_url . '/frontend/paginas/participante/index.php?page=pagamento-erro'
];

foreach ($pages as $page => $url) {
    echo "   - $page: $url\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_NOBODY, true);

    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "     HTTP Code: $http_code - " . ($http_code < 400 ? "✅ OK" : "❌ ERRO") . "\n";

    $test_results["pagina_$page"] = $http_code < 400;
}

// Resumo dos testes
echo "\n=== RESUMO DOS TESTES ===\n";

$total_tests = count($test_results);
$passed_tests = array_sum($test_results);

echo "Total de testes: $total_tests\n";
echo "Testes aprovados: $passed_tests\n";
echo "Taxa de sucesso: " . round(($passed_tests / $total_tests) * 100, 1) . "%\n\n";

echo "Detalhes:\n";
foreach ($test_results as $test => $result) {
    $status = $result ? "✅ PASSOU" : "❌ FALHOU";
    echo "   - $test: $status\n";
}

echo "\n=== URLs PARA TESTE MANUAL ===\n";
echo "1. Página de teste: $base_url/frontend/paginas/participante/index.php?page=teste-pagamento\n";
echo "2. Minhas inscrições: $base_url/frontend/paginas/participante/index.php?page=minhas-inscricoes\n";
echo "3. Pagamento: $base_url/frontend/paginas/participante/index.php?page=pagamento-inscricao&inscricao_id=1\n";

if ($passed_tests === $total_tests) {
    echo "\n🎉 TODOS OS TESTES PASSARAM! Sistema funcionando perfeitamente.\n";
} else {
    echo "\n⚠️ ALGUNS TESTES FALHARAM. Verifique os erros acima.\n";
}

echo "\n=== FIM DO TESTE AUTOMATIZADO ===\n";
