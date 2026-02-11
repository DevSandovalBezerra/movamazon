-- ========================================
-- HOTFIX: Remover constraints FK problemáticas
-- Data: 30/01/2026
-- Problema: FKs referenciam usuarios.id mas admin está em usuario_admin
-- ========================================

-- 1. Remover FK da tabela config (ignorar erro se não existir)
SET @fk_exists = (
    SELECT COUNT(*) 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'config' 
    AND CONSTRAINT_NAME = 'fk_config_admin'
);

SET @sql = IF(@fk_exists > 0, 
    'ALTER TABLE `config` DROP FOREIGN KEY `fk_config_admin`', 
    'SELECT "FK fk_config_admin não existe" AS aviso'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Remover FK da tabela config_historico (CAUSA DO ERRO 500)
SET @fk_exists = (
    SELECT COUNT(*) 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'config_historico' 
    AND CONSTRAINT_NAME = 'fk_config_historico_admin'
);

SET @sql = IF(@fk_exists > 0, 
    'ALTER TABLE `config_historico` DROP FOREIGN KEY `fk_config_historico_admin`', 
    'SELECT "FK fk_config_historico_admin não existe" AS aviso'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Modificar colunas para serem flexíveis (sem constraint)
ALTER TABLE `config` 
MODIFY COLUMN `updated_by` INT DEFAULT NULL 
COMMENT 'ID do admin/organizador que atualizou (referência flexível)';

ALTER TABLE `config_historico` 
MODIFY COLUMN `alterado_por` INT DEFAULT NULL 
COMMENT 'ID do admin/organizador que alterou (referência flexível)';

-- 4. Verificar sucesso
SELECT 'FKs removidas com sucesso!' AS status;