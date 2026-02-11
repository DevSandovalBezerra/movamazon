-- Migration: cria tabela usuario_admin e popula com dados do .env
-- IMPORTANTE: Substitua os valores abaixo pelos valores do seu .env antes de executar
-- Linha 22 do .env: ADMIN_EMAIL
-- Linha 23 do .env: ADMIN_PASSWORD ou ADMIN_PASSWORD_HASH

-- ============================================
-- CONFIGURAÇÕES - SUBSTITUA ESTES VALORES
-- ============================================
-- Opção 1: Se você tem ADMIN_PASSWORD no .env (texto simples)
-- Substitua os valores abaixo:
SET @admin_email = 'admin@movamazon.com.br';  -- Substitua pelo valor de ADMIN_EMAIL do .env (linha 22)
SET @admin_password = 'senha_admin_123';      -- Substitua pelo valor de ADMIN_PASSWORD do .env (linha 23)

-- Opção 2: Se você tem ADMIN_PASSWORD_HASH no .env (hash já gerado)
-- Descomente e use estas linhas em vez das acima:
-- SET @admin_email = 'admin@movamazon.com.br';
-- SET @admin_password_hash = '$2y$10$seu_hash_aqui_gerado_pelo_php';

-- ============================================
-- CRIAÇÃO DA TABELA
-- ============================================
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

-- ============================================
-- PREPARAÇÃO DO HASH DA SENHA
-- ============================================
-- IMPORTANTE: Você DEVE gerar o hash com PHP antes de executar este script
-- 
-- Se você tem ADMIN_PASSWORD_HASH no .env (linha 23):
--   1. Copie o valor de ADMIN_PASSWORD_HASH do .env
--   2. Cole abaixo em @senha_hash
--
-- Se você tem apenas ADMIN_PASSWORD no .env (linha 23):
--   1. Execute no terminal: php -r "echo password_hash('SUA_SENHA_DO_ENV', PASSWORD_DEFAULT);"
--   2. Cole o hash gerado abaixo em @senha_hash

SET @senha_hash = '$2y$10$SUBSTITUA_PELO_HASH_AQUI';

-- ============================================
-- INSERÇÃO DO REGISTRO
-- ============================================
-- Remove registro existente se houver (baseado no email)
DELETE FROM `usuario_admin` WHERE `email` = @admin_email;

-- Insere o novo registro
INSERT INTO `usuario_admin` (
    `nome_completo`,
    `email`,
    `senha`,
    `status`,
    `data_cadastro`
) VALUES (
    'Administrador',
    @admin_email,
    @senha_hash,
    'ativo',
    NOW()
);

-- ============================================
-- VERIFICAÇÃO
-- ============================================
SELECT 
    id,
    nome_completo,
    email,
    status,
    data_cadastro,
    'Registro criado com sucesso!' as mensagem
FROM `usuario_admin` 
WHERE `email` = @admin_email;

