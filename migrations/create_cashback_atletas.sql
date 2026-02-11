-- Migração: Criar tabela de cashback para atletas
-- Data: 2025-12-23
-- Descrição: Sistema de cashback de 1% sobre valor de inscrições (sem extras)

-- Criar tabela de cashback
CREATE TABLE IF NOT EXISTS `cashback_atletas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL,
    `inscricao_id` INT NOT NULL,
    `evento_id` INT NOT NULL,
    `valor_inscricao` DECIMAL(10,2) NOT NULL COMMENT 'Valor base da inscrição (sem extras)',
    `valor_cashback` DECIMAL(10,2) NOT NULL COMMENT 'Valor do cashback (1% do valor_inscricao)',
    `percentual` DECIMAL(5,2) DEFAULT 1.00 COMMENT 'Percentual aplicado (padrão 1%)',
    `status` ENUM('pendente', 'disponivel', 'utilizado', 'expirado') DEFAULT 'disponivel',
    `data_credito` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `data_utilizacao` DATETIME NULL COMMENT 'Data em que o cashback foi utilizado',
    `inscricao_uso_id` INT NULL COMMENT 'ID da inscrição onde o cashback foi aplicado',
    `observacao` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Constraint para evitar duplicidade
    UNIQUE KEY `uk_inscricao` (`inscricao_id`),
    
    -- Índices para performance
    INDEX `idx_usuario` (`usuario_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_evento` (`evento_id`),
    INDEX `idx_data_credito` (`data_credito`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comentário na tabela
ALTER TABLE `cashback_atletas` COMMENT = 'Registro de cashback de 1% sobre inscrições pagas';

