-- Migration: Criar tabela de logs de inscrições e pagamentos
-- Data: 2025-12-30
-- Descrição: Tabela para armazenar logs estruturados de todos os eventos relacionados
--            a inscrições e pagamentos, permitindo auditoria completa e debug facilitado.

SET @dbname = DATABASE();
SET @tablename = 'logs_inscricoes_pagamentos';

-- Verificar se a tabela já existe
SET @table_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = @dbname 
      AND TABLE_NAME = @tablename
);

-- Criar tabela se não existir
SET @sql = IF(@table_exists = 0,
    CONCAT('
CREATE TABLE `', @tablename, '` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `nivel` enum(''ERROR'',''WARNING'',''INFO'',''SUCCESS'') NOT NULL,
  `acao` varchar(100) NOT NULL,
  `inscricao_id` int(11) DEFAULT NULL,
  `payment_id` varchar(100) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `evento_id` int(11) DEFAULT NULL,
  `modalidade_id` int(11) DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `forma_pagamento` varchar(50) DEFAULT NULL,
  `status_pagamento` varchar(50) DEFAULT NULL,
  `mensagem` text,
  `dados_contexto` text COMMENT ''JSON com dados adicionais'',
  `stack_trace` text COMMENT ''Stack trace para erros'',
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inscricao_id` (`inscricao_id`),
  KEY `idx_payment_id` (`payment_id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_nivel` (`nivel`),
  KEY `idx_acao` (`acao`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_evento_id` (`evento_id`),
  KEY `idx_status_pagamento` (`status_pagamento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    '),
    CONCAT('SELECT "Tabela ', @tablename, ' já existe" AS mensagem;')
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

