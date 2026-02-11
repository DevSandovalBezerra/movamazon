-- =====================================================
-- SCRIPT PARA ADICIONAR CAMPO init_point
-- Tabela: pagamentos_ml
-- Data: 2024-12-19
-- =====================================================

-- Verificar se o campo já existe antes de adicionar
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'pagamentos_ml'
    AND COLUMN_NAME = 'init_point'
);

-- Adicionar campo apenas se não existir
SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `pagamentos_ml` ADD COLUMN `init_point` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT ''URL de inicialização do Mercado Pago'' AFTER `preference_id`',
    'SELECT ''Campo init_point já existe'' as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar estrutura final
SELECT 'Estrutura da tabela pagamentos_ml após atualização:' as info;
DESCRIBE pagamentos_ml;
