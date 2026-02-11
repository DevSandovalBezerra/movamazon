<?php

/**
 * Script para testar diretamente a API e identificar o problema
 */

echo "=== DIAGNÓSTICO COMPLETO DA API get_inscricao.php ===\n\n";

// Simular sessão
session_start();
$_SESSION['user_id'] = 4;
$_SESSION['user_name'] = 'Daniel Dias Filho';
$_SESSION['user_email'] = 'daniel@gmail.com';
$_SESSION['papel'] = 'participante';

echo "1. ✅ Sessão configurada:\n";
echo "   - User ID: " . $_SESSION['user_id'] . "\n";
echo "   - User Name: " . $_SESSION['user_name'] . "\n\n";

// Simular parâmetro GET
$_GET['inscricao_id'] = 1;

echo "2. ✅ Parâmetro configurado:\n";
echo "   - inscricao_id: " . $_GET['inscricao_id'] . "\n\n";

echo "3. 🔍 Testando API diretamente:\n";

// Capturar output e erros
ob_start();
$old_error_reporting = error_reporting(E_ALL);
$old_display_errors = ini_set('display_errors', 1);

try {
    include __DIR__ . '/../api/participante/get_inscricao.php';
    $output = ob_get_clean();

    echo "   - API executada sem erros fatais\n";
    echo "   - Output capturado: " . strlen($output) . " caracteres\n\n";

    echo "4. 📋 Resposta da API:\n";
    echo "--- INÍCIO DA RESPOSTA ---\n";
    echo $output . "\n";
    echo "--- FIM DA RESPOSTA ---\n\n";

    // Tentar decodificar JSON
    $data = json_decode($output, true);
    if ($data) {
        echo "5. ✅ JSON válido:\n";
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
        echo "5. ❌ JSON inválido:\n";
        echo "   - Erro: " . json_last_error_msg() . "\n";
        echo "   - Primeiros 200 caracteres: " . substr($output, 0, 200) . "\n";
    }
} catch (Exception $e) {
    $output = ob_get_clean();
    echo "   - ❌ ERRO FATAL: " . $e->getMessage() . "\n";
    echo "   - Output: " . $output . "\n";
} catch (Error $e) {
    $output = ob_get_clean();
    echo "   - ❌ ERRO CRÍTICO: " . $e->getMessage() . "\n";
    echo "   - Output: " . $output . "\n";
}

// Restaurar configurações
error_reporting($old_error_reporting);
ini_set('display_errors', $old_display_errors);

echo "\n6. 🔍 Verificando arquivos:\n";

$files_to_check = [
    __DIR__ . '/../api/participante/get_inscricao.php' => 'API principal',
    __DIR__ . '/../api/db.php' => 'Conexão com banco',
    __DIR__ . '/../api/security_middleware.php' => 'Middleware de segurança'
];

foreach ($files_to_check as $file => $description) {
    $exists = file_exists($file);
    echo "   - $file ($description): " . ($exists ? "✅ Existe" : "❌ Não existe") . "\n";

    if ($exists) {
        $size = filesize($file);
        echo "     - Tamanho: $size bytes\n";
    }
}

echo "\n7. 🔍 Verificando logs de erro:\n";
$log_file = __DIR__ . '/../logs/php_errors.log';
if (file_exists($log_file)) {
    $logs = file_get_contents($log_file);
    $lines = explode("\n", $logs);
    $recent_logs = array_slice($lines, -5); // Últimas 5 linhas

    echo "   - Últimas 5 linhas do log:\n";
    foreach ($recent_logs as $line) {
        if (trim($line)) {
            echo "     " . $line . "\n";
        }
    }
} else {
    echo "   - Arquivo de log não encontrado\n";
}

echo "\n=== RESUMO DO DIAGNÓSTICO ===\n";
echo "✅ Script executado com sucesso\n";
echo "✅ Sessão configurada\n";
echo "✅ Parâmetros configurados\n";
echo "✅ API testada diretamente\n\n";

echo "🔗 URLs para teste:\n";
echo "1. Página de debug: http://localhost/movamazonas/frontend/paginas/participante/index.php?page=pagamento-debug&inscricao_id=1\n";
echo "2. Página original: http://localhost/movamazonas/frontend/paginas/participante/index.php?page=pagamento-inscricao&inscricao_id=1\n";

echo "\n=== FIM DO DIAGNÓSTICO ===\n";
