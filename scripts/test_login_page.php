<?php

/**
 * Script para testar a página de login
 */

echo "=== TESTE DA PÁGINA DE LOGIN ===\n\n";

// Simular sessão
session_start();

echo "1. Testando página de login:\n";

$base_url = 'http://localhost/movamazonas';
$url = $base_url . '/frontend/paginas/auth/login.php';

echo "   - URL: $url\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   - HTTP Code: $http_code\n";

// Verificar elementos importantes
$has_title = strpos($result, 'Entrar na sua conta') !== false;
$has_email_field = strpos($result, 'name="email"') !== false;
$has_password_field = strpos($result, 'name="password"') !== false;
$has_submit_button = strpos($result, 'type="submit"') !== false;
$has_entrar_text = strpos($result, 'Entrar') !== false;

echo "   - Título presente: " . ($has_title ? "✅ SIM" : "❌ NÃO") . "\n";
echo "   - Campo email: " . ($has_email_field ? "✅ SIM" : "❌ NÃO") . "\n";
echo "   - Campo senha: " . ($has_password_field ? "✅ SIM" : "❌ NÃO") . "\n";
echo "   - Botão submit: " . ($has_submit_button ? "✅ SIM" : "❌ NÃO") . "\n";
echo "   - Texto 'Entrar': " . ($has_entrar_text ? "✅ SIM" : "❌ NÃO") . "\n";

// Verificar CSS
$has_primary_classes = strpos($result, 'primary-600') !== false;
$has_custom_css = strpos($result, 'custom.css') !== false;
$has_tailwind = strpos($result, 'tailwind.min.css') !== false;

echo "\n2. Verificando CSS:\n";
echo "   - Classes primary-600: " . ($has_primary_classes ? "✅ SIM" : "❌ NÃO") . "\n";
echo "   - custom.css carregado: " . ($has_custom_css ? "✅ SIM" : "❌ NÃO") . "\n";
echo "   - tailwind.min.css carregado: " . ($has_tailwind ? "✅ SIM" : "❌ NÃO") . "\n";

// Verificar se há erros PHP
$has_php_errors = strpos($result, 'Fatal error') !== false || strpos($result, 'Parse error') !== false;
echo "   - Erros PHP: " . ($has_php_errors ? "❌ SIM" : "✅ NÃO") . "\n";

echo "\n3. Verificando arquivos CSS:\n";

$css_files = [
    'frontend/assets/css/custom.css' => 'CSS customizado',
    'frontend/assets/css/tailwind.min.css' => 'Tailwind CSS'
];

foreach ($css_files as $file => $description) {
    $exists = file_exists($file);
    echo "   - $file ($description): " . ($exists ? "✅ Existe" : "❌ Não existe") . "\n";

    if ($exists) {
        $content = file_get_contents($file);
        $has_primary = strpos($content, 'primary-600') !== false;
        echo "     - Classes primary-600: " . ($has_primary ? "✅ SIM" : "❌ NÃO") . "\n";
    }
}

echo "\n=== RESUMO ===\n";
if ($has_submit_button && $has_entrar_text && $has_custom_css) {
    echo "✅ Página de login funcionando corretamente\n";
    echo "✅ Botão 'Entrar' presente\n";
    echo "✅ CSS carregado\n";
} else {
    echo "❌ Problemas encontrados na página de login\n";
    if (!$has_submit_button) echo "   - Botão submit ausente\n";
    if (!$has_entrar_text) echo "   - Texto 'Entrar' ausente\n";
    if (!$has_custom_css) echo "   - CSS customizado não carregado\n";
}

echo "\n🎉 TESTE CONCLUÍDO!\n";
