-- Migration: cria tabelas de configuração administrativa
-- Executar uma única vez após atualizar o código

CREATE TABLE IF NOT EXISTS `config` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `chave` VARCHAR(100) NOT NULL,
  `valor` TEXT,
  `tipo` ENUM('string','number','boolean','json','encrypted') DEFAULT 'string',
  `categoria` VARCHAR(50) NOT NULL,
  `descricao` TEXT,
  `editavel` TINYINT(1) DEFAULT 1,
  `visivel` TINYINT(1) DEFAULT 1,
  `validacao` TEXT COMMENT 'Regras em JSON',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` INT DEFAULT NULL COMMENT 'ID do admin que atualizou',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_chave` (`chave`),
  KEY `idx_categoria` (`categoria`),
  KEY `idx_updated_by` (`updated_by`),
  CONSTRAINT `fk_config_admin` FOREIGN KEY (`updated_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `config_historico` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `config_id` INT NOT NULL,
  `chave` VARCHAR(100) NOT NULL,
  `valor_antigo` TEXT,
  `valor_novo` TEXT,
  `alterado_por` INT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_config_id` (`config_id`),
  KEY `idx_chave` (`chave`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_config_historico_config` FOREIGN KEY (`config_id`) REFERENCES `config` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_config_historico_admin` FOREIGN KEY (`alterado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `config` (`chave`, `valor`, `tipo`, `categoria`, `descricao`)
VALUES
  ('sistema.nome', 'MovAmazonas', 'string', 'sistema', 'Nome exibido em títulos e e-mails'),
  ('sistema.url', '', 'string', 'sistema', 'URL base do sistema (ex.: https://movamazonas.com)'),
  ('sistema.timezone', 'America/Manaus', 'string', 'sistema', 'Timezone padrão do sistema'),
  ('sistema.idioma', 'pt-BR', 'string', 'sistema', 'Idioma padrão das interfaces'),
  ('sistema.manutencao', 'false', 'boolean', 'sistema', 'Habilita modo manutenção'),
  ('sistema.manutencao_mensagem', '', 'string', 'sistema', 'Mensagem exibida no modo manutenção'),
  ('sistema.logs_retention_days', '90', 'number', 'sistema', 'Dias para retenção de logs'),
  ('sistema.max_upload_size', '5242880', 'number', 'sistema', 'Tamanho máximo de upload (bytes)');

