<?php

/**
 * Explicação sobre session_start() seguro
 */

echo "=== EXPLICAÇÃO: SESSION_START() SEGURO ===\n\n";

echo "❌ PROBLEMA COM session_start() SIMPLES:\n";
echo "```php\n";
echo "session_start(); // Sempre executa, mesmo se já ativa\n";
echo "```\n";
echo "- Causa erro: 'session_start(): Ignoring session_start() because a session is already active'\n";
echo "- Pode quebrar APIs quando chamadas de páginas que já iniciaram sessão\n\n";

echo "✅ SOLUÇÃO COM VERIFICAÇÃO:\n";
echo "```php\n";
echo "if (session_status() === PHP_SESSION_NONE) {\n";
echo "    session_start();\n";
echo "}\n";
echo "```\n";
echo "- Só inicia sessão se não estiver ativa\n";
echo "- Evita erros de sessão duplicada\n";
echo "- Funciona tanto em páginas quanto em APIs\n\n";

echo "🔍 CONSTANTES DE STATUS DA SESSÃO:\n";
echo "- PHP_SESSION_DISABLED (0): Sessões desabilitadas no PHP\n";
echo "- PHP_SESSION_NONE (1): Sessões habilitadas, mas nenhuma iniciada\n";
echo "- PHP_SESSION_ACTIVE (2): Sessão ativa\n\n";

echo "📋 ALTERNATIVAS:\n";
echo "1. session_status() === PHP_SESSION_NONE (RECOMENDADO)\n";
echo "2. !isset($_SESSION) (menos confiável)\n";
echo "3. session_id() === '' (funciona, mas menos claro)\n\n";

echo "🎯 BENEFÍCIOS:\n";
echo "✅ Evita erros de sessão duplicada\n";
echo "✅ APIs funcionam tanto standalone quanto incluídas\n";
echo "✅ Código mais robusto e profissional\n";
echo "✅ Compatível com diferentes cenários de uso\n\n";

echo "🔧 ARQUIVOS CORRIGIDOS:\n";
echo "✅ api/participante/get_inscricao.php\n";
echo "✅ api/participante/get_inscricoes.php\n";
echo "✅ api/mercadolivre/create_payment.php\n\n";

echo "🚀 PRÓXIMOS PASSOS:\n";
echo "1. Aplicar a mesma correção em outras APIs\n";
echo "2. Testar se os erros de sessão foram resolvidos\n";
echo "3. Verificar se as páginas de pagamento funcionam\n\n";

echo "💡 DICA:\n";
echo "Sempre use esta verificação em APIs que podem ser chamadas\n";
echo "de diferentes contextos (páginas, AJAX, includes, etc.)\n";
