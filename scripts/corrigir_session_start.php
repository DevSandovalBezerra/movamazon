<?php

/**
 * Script para corrigir session_start() em todas as APIs
 */

echo "=== CORREÇÃO DE SESSION_START EM TODAS AS APIs ===\n\n";

$api_files = [
    'api/participante/get_inscricoes.php',
    'api/mercadolivre/create_payment.php',
    'api/mercadolivre/webhook.php',
    'api/inscricao/save_inscricao.php',
    'api/inscricao/precreate.php',
    'api/auth/login.php',
    'api/auth/logout.php',
    'api/auth/check_session.php',
    'api/auth/auth.php'
];

$corrigidos = 0;
$erros = 0;

foreach ($api_files as $file) {
    if (file_exists($file)) {
        echo "🔧 Corrigindo: $file\n";

        $content = file_get_contents($file);

        // Verificar se já tem a verificação
        if (strpos($content, 'session_status() === PHP_SESSION_NONE') !== false) {
            echo "   ✅ Já corrigido\n";
            continue;
        }

        // Substituir session_start() simples pela verificação
        $old_pattern = '/^<\?php\s*session_start\(\);/m';
        $new_replacement = '<?php
// Verificar se a sessão já está ativa antes de iniciar
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}';

        $new_content = preg_replace($old_pattern, $new_replacement, $content);

        if ($new_content !== $content) {
            file_put_contents($file, $new_content);
            echo "   ✅ Corrigido\n";
            $corrigidos++;
        } else {
            echo "   ⚠️ Não encontrou padrão para corrigir\n";
        }
    } else {
        echo "❌ Arquivo não encontrado: $file\n";
        $erros++;
    }
}

echo "\n=== RESUMO ===\n";
echo "✅ Arquivos corrigidos: $corrigidos\n";
echo "❌ Erros: $erros\n";
echo "🎉 Correção concluída!\n\n";

echo "📋 EXPLICAÇÃO DA CORREÇÃO:\n";
echo "- ANTES: session_start(); (sempre executa)\n";
echo "- DEPOIS: if (session_status() === PHP_SESSION_NONE) { session_start(); }\n";
echo "- BENEFÍCIO: Evita erro 'session already started'\n\n";

echo "🔍 STATUS DA SESSÃO:\n";
echo "- PHP_SESSION_DISABLED: Sessões desabilitadas\n";
echo "- PHP_SESSION_NONE: Sessões habilitadas, mas nenhuma iniciada\n";
echo "- PHP_SESSION_ACTIVE: Sessão ativa\n";
