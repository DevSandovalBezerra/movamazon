-- Migration: Adicionar campo data_expiracao_pagamento na tabela inscricoes
-- Data: 2025-12-25
-- Descrição: Adiciona campo para armazenar data de expiração de boletos e outros métodos de pagamento.
--            Permite cancelamento automático de inscrições com pagamentos expirados.

SET @dbname = DATABASE();
SET @tablename = 'inscricoes';

-- Verificar se a coluna já existe antes de adicionar
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname 
      AND TABLE_NAME = @tablename 
      AND COLUMN_NAME = 'data_expiracao_pagamento'
);

-- Adicionar coluna se não existir
SET @sql = IF(@col_exists = 0,
    CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `data_expiracao_pagamento` DATETIME NULL DEFAULT NULL COMMENT ''Data de expiração do boleto ou outro método de pagamento'' AFTER `data_pagamento`;'),
    'SELECT "Coluna data_expiracao_pagamento já existe" AS mensagem;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Criar índices para otimizar consultas de cancelamento
-- Verificar se índice já existe antes de criar
SET @idx_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = @dbname 
      AND TABLE_NAME = @tablename 
      AND INDEX_NAME = 'idx_data_expiracao_pagamento'
);

SET @sql_idx1 = IF(@idx_exists = 0,
    CONCAT('CREATE INDEX `idx_data_expiracao_pagamento` ON `', @tablename, '` (`data_expiracao_pagamento`);'),
    'SELECT "Índice idx_data_expiracao_pagamento já existe" AS mensagem;'
);

PREPARE stmt_idx1 FROM @sql_idx1;
EXECUTE stmt_idx1;
DEALLOCATE PREPARE stmt_idx1;

-- Criar índice composto para otimizar consulta de cancelamento por status e data
SET @idx_exists2 = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = @dbname 
      AND TABLE_NAME = @tablename 
      AND INDEX_NAME = 'idx_status_data_inscricao'
);

SET @sql_idx2 = IF(@idx_exists2 = 0,
    CONCAT('CREATE INDEX `idx_status_data_inscricao` ON `', @tablename, '` (`status_pagamento`, `data_inscricao`);'),
    'SELECT "Índice idx_status_data_inscricao já existe" AS mensagem;'
);

PREPARE stmt_idx2 FROM @sql_idx2;
EXECUTE stmt_idx2;
DEALLOCATE PREPARE stmt_idx2;

SELECT 'Migration concluída: Campo data_expiracao_pagamento adicionado com sucesso' AS resultado;

