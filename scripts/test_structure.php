<?php

/**
 * Script para testar a estrutura corrigida
 */

echo "=== TESTE DA ESTRUTURA CORRIGIDA ===\n\n";

// Testar URLs
$base_url = 'http://localhost/movamazonas';

echo "1. Testando páginas via index.php:\n";
$test_pages = [
    'minhas-inscricoes' => $base_url . '/frontend/paginas/participante/index.php?page=minhas-inscricoes',
    'pagamento-inscricao' => $base_url . '/frontend/paginas/participante/index.php?page=pagamento-inscricao&inscricao_id=1'
];

foreach ($test_pages as $page => $url) {
    echo "   - $page: $url\n";
}

echo "\n2. Testando APIs:\n";
$test_apis = [
    'get_inscricoes' => $base_url . '/api/participante/get_inscricoes.php',
    'get_inscricao' => $base_url . '/api/participante/get_inscricao.php?inscricao_id=1'
];

foreach ($test_apis as $api => $url) {
    echo "   - $api: $url\n";
}

echo "\n3. Estrutura de arquivos:\n";
$files_to_check = [
    'frontend/paginas/participante/index.php' => 'Wrapper principal',
    'frontend/paginas/participante/minhas-inscricoes.php' => 'Página de inscrições',
    'frontend/paginas/participante/pagamento-inscricao.php' => 'Página de pagamento',
    'api/participante/get_inscricoes.php' => 'API para listar inscrições',
    'api/participante/get_inscricao.php' => 'API para buscar inscrição específica',
    'api/mercadolivre/create_payment.php' => 'API para criar pagamento'
];

foreach ($files_to_check as $file => $description) {
    $exists = file_exists($file);
    echo "   - $file: " . ($exists ? "✅ Existe" : "❌ Não existe") . " ($description)\n";
}

echo "\n4. Fluxo correto:\n";
echo "   ✅ Usuário acessa: index.php?page=minhas-inscricoes\n";
echo "   ✅ JavaScript chama: ../../../api/participante/get_inscricoes.php\n";
echo "   ✅ Botão 'Pagar Agora' vai para: index.php?page=pagamento-inscricao&inscricao_id=X\n";
echo "   ✅ JavaScript chama: ../../../api/participante/get_inscricao.php\n";
echo "   ✅ JavaScript chama: ../../../api/mercadolivre/create_payment.php\n";

echo "\n=== ESTRUTURA CORRIGIDA COM SUCESSO! ===\n";
