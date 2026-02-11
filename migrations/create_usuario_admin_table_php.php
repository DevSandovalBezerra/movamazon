<?php
/**
 * Script PHP para criar tabela usuario_admin e popular com dados do .env
 * 
 * USO:
 * 1. Configure o .env com ADMIN_EMAIL e ADMIN_PASSWORD ou ADMIN_PASSWORD_HASH
 * 2. Execute: php migrations/create_usuario_admin_table_php.php
 */

// Carregar dotenv
require_once __DIR__ . '/../vendor/autoload.php';
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} catch (Exception $e) {
    // Continua mesmo se .env não existir
}

// A função envValue() será carregada do db.php

// Conectar ao banco (db.php já tem envValue)
require_once __DIR__ . '/../api/db.php';

// Carregar variáveis do .env
$admin_email = envValue('ADMIN_EMAIL');
$admin_password = envValue('ADMIN_PASSWORD');
$admin_password_hash = envValue('ADMIN_PASSWORD_HASH');

if (empty($admin_email)) {
    die("ERRO: ADMIN_EMAIL não configurado no .env\n");
}

// Determinar hash da senha
if (!empty($admin_password_hash)) {
    $senha_hash = $admin_password_hash;
} elseif (!empty($admin_password)) {
    // Gerar hash usando password_hash do PHP (recomendado)
    $senha_hash = password_hash($admin_password, PASSWORD_DEFAULT);
} else {
    die("ERRO: ADMIN_PASSWORD ou ADMIN_PASSWORD_HASH não configurado no .env\n");
}

try {
    if (!$pdo->inTransaction()) {
        $pdo->beginTransaction();
    }

    // Criar tabela
    $sql_create = "
    CREATE TABLE IF NOT EXISTS `usuario_admin` (
      `id` INT NOT NULL AUTO_INCREMENT,
      `nome_completo` VARCHAR(255) NOT NULL DEFAULT 'Administrador',
      `email` VARCHAR(100) NOT NULL,
      `senha` VARCHAR(255) NOT NULL,
      `status` ENUM('ativo','inativo') DEFAULT 'ativo',
      `data_cadastro` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
      `ultimo_acesso` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
      `token_recuperacao` VARCHAR(255) DEFAULT NULL,
      `token_expira` DATETIME DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `email` (`email`),
      KEY `idx_status` (`status`),
      KEY `idx_token_recuperacao` (`token_recuperacao`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($sql_create);
    echo "✓ Tabela 'usuario_admin' criada/verificada\n";

    // Remover registro existente (se houver)
    $stmt = $pdo->prepare("DELETE FROM `usuario_admin` WHERE `email` = ?");
    $stmt->execute([$admin_email]);
    
    if ($stmt->rowCount() > 0) {
        echo "✓ Registro existente removido\n";
    }

    // Inserir novo registro
    $stmt = $pdo->prepare("
        INSERT INTO `usuario_admin` (
            `nome_completo`,
            `email`,
            `senha`,
            `status`,
            `data_cadastro`
        ) VALUES (
            'Administrador',
            ?,
            ?,
            'ativo',
            NOW()
        )
    ");

    $stmt->execute([$admin_email, $senha_hash]);
    echo "✓ Registro inserido com sucesso\n";

    // Verificar
    $stmt = $pdo->prepare("SELECT * FROM `usuario_admin` WHERE `email` = ?");
    $stmt->execute([$admin_email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo "\n=== REGISTRO CRIADO ===\n";
        echo "ID: {$admin['id']}\n";
        echo "Nome: {$admin['nome_completo']}\n";
        echo "Email: {$admin['email']}\n";
        echo "Status: {$admin['status']}\n";
        echo "Data Cadastro: {$admin['data_cadastro']}\n";
        echo "\n✓ Sucesso! Tabela usuario_admin criada e populada.\n";
    }

    if ($pdo->inTransaction()) {
        $pdo->commit();
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("ERRO: " . $e->getMessage() . "\n");
}

