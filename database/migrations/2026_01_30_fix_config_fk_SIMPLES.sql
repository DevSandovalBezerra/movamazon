-- ========================================
-- VERSÃO SIMPLIFICADA - Para phpMyAdmin
-- Execute LINHA POR LINHA se a versão automática der erro
-- ========================================

-- PASSO 1: Remover FK da tabela config
ALTER TABLE `config` DROP FOREIGN KEY `fk_config_admin`;
-- (Se der erro "constraint não existe", ignore e continue)

-- PASSO 2: Remover FK da tabela config_historico
ALTER TABLE `config_historico` DROP FOREIGN KEY `fk_config_historico_admin`;
-- (Se der erro "constraint não existe", ignore e continue)

-- PASSO 3: Modificar coluna config.updated_by
ALTER TABLE `config` 
MODIFY COLUMN `updated_by` INT DEFAULT NULL 
COMMENT 'ID do admin/organizador que atualizou (referência flexível)';

-- PASSO 4: Modificar coluna config_historico.alterado_por
ALTER TABLE `config_historico` 
MODIFY COLUMN `alterado_por` INT DEFAULT NULL 
COMMENT 'ID do admin/organizador que alterou (referência flexível)';

-- PASSO 5: Verificar se funcionou
SELECT 'Migration executada com sucesso!' AS status;