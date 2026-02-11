-- ========================================
-- VERSÃO FINAL - Execute apenas os comandos necessários
-- ========================================

-- COMANDO 1: Remover FK da tabela config_historico (CRÍTICO!)
ALTER TABLE `config_historico` DROP FOREIGN KEY `fk_config_historico_admin`;

-- COMANDO 2: Modificar coluna config.updated_by
ALTER TABLE `config` 
MODIFY COLUMN `updated_by` INT DEFAULT NULL 
COMMENT 'ID do admin/organizador que atualizou';

-- COMANDO 3: Modificar coluna config_historico.alterado_por
ALTER TABLE `config_historico` 
MODIFY COLUMN `alterado_por` INT DEFAULT NULL 
COMMENT 'ID do admin/organizador que alterou';

-- COMANDO 4: Verificar se funcionou
SELECT 'Migration concluída com sucesso!' AS status;