<?php
/**
 * Script para testar login do admin
 */

require_once __DIR__ . '/../api/db.php';
require_once __DIR__ . '/../api/admin/auth_middleware.php';

echo "=== TESTE DE LOGIN DO ADMIN ===\n\n";

// Carregar credenciais do .env
$admin_email = envValue('ADMIN_EMAIL', 'admin@movamazon.com.br');
$admin_password = envValue('ADMIN_PASSWORD');

if (empty($admin_password)) {
    echo "⚠️  ADMIN_PASSWORD não configurado no .env\n";
    echo "Por favor, configure ADMIN_PASSWORD no .env\n";
    exit(1);
}

echo "Testando login com:\n";
echo "  Email: {$admin_email}\n";
echo "  Senha: ***\n\n";

// Testar autenticação
$result = autenticarAdmin($admin_email, $admin_password);

if ($result['success']) {
    echo "✓ LOGIN BEM-SUCEDIDO!\n\n";
    echo "Dados da sessão:\n";
    echo "  ID: " . ($_SESSION['user_id'] ?? 'N/A') . "\n";
    echo "  Email: " . ($_SESSION['user_email'] ?? 'N/A') . "\n";
    echo "  Nome: " . ($_SESSION['user_name'] ?? 'N/A') . "\n";
    echo "  Papel: " . ($_SESSION['papel'] ?? 'N/A') . "\n";
    echo "\n✓ Você pode fazer login no sistema agora!\n";
} else {
    echo "❌ FALHA NO LOGIN\n";
    echo "Mensagem: {$result['message']}\n\n";
    
    // Verificar o que está no banco
    $stmt = $pdo->prepare("SELECT id, email, status FROM usuario_admin WHERE email = ?");
    $stmt->execute([$admin_email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "Admin encontrado no banco:\n";
        echo "  ID: {$admin['id']}\n";
        echo "  Email: {$admin['email']}\n";
        echo "  Status: {$admin['status']}\n";
        echo "\nO problema pode ser a senha. Verifique se ADMIN_PASSWORD no .env está correto.\n";
    } else {
        echo "❌ Admin não encontrado no banco com email: {$admin_email}\n";
        echo "Execute: php migrations/corrigir_email_admin.php\n";
    }
}

