<?php
/**
 * Script para corrigir o email do admin
 * Corrige o email de "mail.movamazon.com.br" para "admin@movamazon.com.br"
 */

require_once __DIR__ . '/../api/db.php';

echo "=== CORREÇÃO DE EMAIL DO ADMIN ===\n\n";

try {
    // Verificar email atual
    $stmt = $pdo->query("SELECT id, email FROM usuario_admin LIMIT 1");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        echo "❌ Nenhum admin encontrado no banco\n";
        exit(1);
    }
    
    echo "Email atual no banco: {$admin['email']}\n";
    
    // Definir email correto
    $email_correto = 'admin@movamazon.com.br';
    
    if ($admin['email'] === $email_correto) {
        echo "✓ Email já está correto!\n";
        exit(0);
    }
    
    echo "Email correto: {$email_correto}\n\n";
    
    // Atualizar email
    $update = $pdo->prepare("UPDATE usuario_admin SET email = ? WHERE id = ?");
    $update->execute([$email_correto, $admin['id']]);
    
    echo "✓ Email atualizado com sucesso!\n";
    echo "Agora você pode fazer login com:\n";
    echo "  Email: {$email_correto}\n";
    echo "  Senha: (a senha do ADMIN_PASSWORD do .env)\n\n";
    
    // Verificar
    $stmt = $pdo->prepare("SELECT * FROM usuario_admin WHERE email = ?");
    $stmt->execute([$email_correto]);
    $verificacao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($verificacao) {
        echo "=== VERIFICAÇÃO ===\n";
        echo "ID: {$verificacao['id']}\n";
        echo "Nome: {$verificacao['nome_completo']}\n";
        echo "Email: {$verificacao['email']}\n";
        echo "Status: {$verificacao['status']}\n";
        echo "\n✓ Tudo OK! Pode fazer login agora.\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    exit(1);
}

