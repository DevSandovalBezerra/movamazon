<?php
/**
 * Script para corrigir/verificar usuario_admin
 * 
 * Este script:
 * 1. Verifica se a tabela existe
 * 2. Lista os registros existentes
 * 3. Permite corrigir o email
 * 4. Permite recriar o registro com dados do .env
 */

require_once __DIR__ . '/../vendor/autoload.php';
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} catch (Exception $e) {
    // Continua mesmo se .env não existir
}

require_once __DIR__ . '/../api/db.php';

// A função envValue() será carregada do db.php

echo "=== VERIFICAÇÃO E CORREÇÃO DE usuario_admin ===\n\n";

try {
    // Verificar se a tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuario_admin'");
    if ($stmt->rowCount() === 0) {
        echo "❌ Tabela 'usuario_admin' não existe!\n";
        echo "Execute primeiro: php migrations/create_usuario_admin_table_php.php\n";
        exit(1);
    }
    echo "✓ Tabela 'usuario_admin' existe\n\n";

    // Listar registros existentes
    $stmt = $pdo->query("SELECT id, nome_completo, email, status, data_cadastro FROM usuario_admin");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($admins)) {
        echo "⚠️  Nenhum registro encontrado na tabela\n\n";
    } else {
        echo "=== REGISTROS EXISTENTES ===\n";
        foreach ($admins as $admin) {
            echo "ID: {$admin['id']}\n";
            echo "Nome: {$admin['nome_completo']}\n";
            echo "Email: {$admin['email']}\n";
            echo "Status: {$admin['status']}\n";
            echo "Data Cadastro: {$admin['data_cadastro']}\n";
            echo "---\n";
        }
        echo "\n";
    }

    // Verificar .env
    $admin_email = envValue('ADMIN_EMAIL');
    $admin_password = envValue('ADMIN_PASSWORD');
    $admin_password_hash = envValue('ADMIN_PASSWORD_HASH');

    echo "=== CONFIGURAÇÕES DO .ENV ===\n";
    echo "ADMIN_EMAIL: " . ($admin_email ?: 'NÃO CONFIGURADO') . "\n";
    echo "ADMIN_PASSWORD: " . ($admin_password ? '*** (configurado)' : 'NÃO CONFIGURADO') . "\n";
    echo "ADMIN_PASSWORD_HASH: " . ($admin_password_hash ? '*** (configurado)' : 'NÃO CONFIGURADO') . "\n\n";

    if (empty($admin_email)) {
        echo "❌ ADMIN_EMAIL não configurado no .env\n";
        exit(1);
    }

    // Verificar se o email do .env existe no banco
    $stmt = $pdo->prepare("SELECT * FROM usuario_admin WHERE email = ?");
    $stmt->execute([$admin_email]);
    $admin_existente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin_existente) {
        echo "✓ Email do .env encontrado no banco: {$admin_email}\n";
        echo "ID: {$admin_existente['id']}\n";
        echo "Status: {$admin_existente['status']}\n\n";
        
        // Testar senha se fornecida
        if (!empty($admin_password) || !empty($admin_password_hash)) {
            $senha_hash = !empty($admin_password_hash) ? $admin_password_hash : password_hash($admin_password, PASSWORD_DEFAULT);
            
            if (password_verify($admin_password, $admin_existente['senha']) || 
                (!empty($admin_password_hash) && $admin_existente['senha'] === $admin_password_hash)) {
                echo "✓ Senha está correta no banco\n";
            } else {
                echo "⚠️  Senha no banco não corresponde ao .env\n";
                echo "Deseja atualizar a senha? (S/N): ";
                $resposta = trim(fgets(STDIN));
                if (strtoupper($resposta) === 'S') {
                    $novo_hash = !empty($admin_password_hash) ? $admin_password_hash : password_hash($admin_password, PASSWORD_DEFAULT);
                    $update = $pdo->prepare("UPDATE usuario_admin SET senha = ? WHERE id = ?");
                    $update->execute([$novo_hash, $admin_existente['id']]);
                    echo "✓ Senha atualizada!\n";
                }
            }
        }
    } else {
        echo "⚠️  Email do .env NÃO encontrado no banco\n";
        echo "Email no .env: {$admin_email}\n\n";
        
        // Verificar se há emails incorretos (sem @)
        $stmt = $pdo->query("SELECT id, email FROM usuario_admin WHERE email NOT LIKE '%@%'");
        $emails_incorretos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($emails_incorretos)) {
            echo "=== EMAILS INCORRETOS ENCONTRADOS (sem @) ===\n";
            foreach ($emails_incorretos as $incorreto) {
                echo "ID: {$incorreto['id']} - Email: {$incorreto['email']}\n";
            }
            echo "\n";
        }
        
        echo "Deseja criar/atualizar o registro com o email do .env? (S/N): ";
        $resposta = trim(fgets(STDIN));
        
        if (strtoupper($resposta) === 'S') {
            // Determinar hash da senha
            if (!empty($admin_password_hash)) {
                $senha_hash = $admin_password_hash;
            } elseif (!empty($admin_password)) {
                $senha_hash = password_hash($admin_password, PASSWORD_DEFAULT);
            } else {
                echo "❌ ADMIN_PASSWORD ou ADMIN_PASSWORD_HASH não configurado\n";
                exit(1);
            }

            // Remover registros existentes com email incorreto
            $delete = $pdo->prepare("DELETE FROM usuario_admin WHERE email NOT LIKE '%@%' OR email = ?");
            $delete->execute([$admin_email]);
            
            // Inserir novo registro
            $insert = $pdo->prepare("
                INSERT INTO usuario_admin (nome_completo, email, senha, status, data_cadastro)
                VALUES ('Administrador', ?, ?, 'ativo', NOW())
                ON DUPLICATE KEY UPDATE
                    nome_completo = 'Administrador',
                    senha = VALUES(senha),
                    status = 'ativo'
            ");
            $insert->execute([$admin_email, $senha_hash]);
            
            echo "✓ Registro criado/atualizado com sucesso!\n";
            echo "Email: {$admin_email}\n";
            echo "Status: ativo\n\n";
        }
    }

    // Verificação final
    echo "=== VERIFICAÇÃO FINAL ===\n";
    $stmt = $pdo->prepare("SELECT * FROM usuario_admin WHERE email = ? AND status = 'ativo'");
    $stmt->execute([$admin_email]);
    $admin_final = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin_final) {
        echo "✓ Tudo OK! Você pode fazer login com:\n";
        echo "  Email: {$admin_final['email']}\n";
        echo "  Senha: (a senha do ADMIN_PASSWORD do .env)\n";
    } else {
        echo "❌ Ainda há problemas. Verifique manualmente.\n";
    }

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    exit(1);
}

