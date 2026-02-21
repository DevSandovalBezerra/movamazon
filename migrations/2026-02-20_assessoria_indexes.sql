-- ==========================================================
-- Migracao: Indexes complementares do modulo Assessoria
-- Data: 2026-02-20
-- Descricao: Adiciona indexes de performance nas colunas
--            status e filtros compostos frequentes
-- IMPORTANTE: Executar DEPOIS das migrations anteriores
-- Compativel com MySQL 5.7+
-- ==========================================================

SET @db = DATABASE();

-- assessorias.status
SET @idx = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'assessorias' AND INDEX_NAME = 'idx_assessorias_status');
SET @sql = IF(@idx = 0, 'ALTER TABLE assessorias ADD INDEX idx_assessorias_status (status)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- assessoria_equipe.status
SET @idx = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'assessoria_equipe' AND INDEX_NAME = 'idx_assessoria_equipe_status');
SET @sql = IF(@idx = 0, 'ALTER TABLE assessoria_equipe ADD INDEX idx_assessoria_equipe_status (status)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- assessoria_atletas.status
SET @idx = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'assessoria_atletas' AND INDEX_NAME = 'idx_assessoria_atletas_status');
SET @sql = IF(@idx = 0, 'ALTER TABLE assessoria_atletas ADD INDEX idx_assessoria_atletas_status (status)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- assessoria_programas.status
SET @idx = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'assessoria_programas' AND INDEX_NAME = 'idx_assessoria_programas_status');
SET @sql = IF(@idx = 0, 'ALTER TABLE assessoria_programas ADD INDEX idx_assessoria_programas_status (status)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- assessoria_convites: compostos para queries frequentes
SET @idx = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'assessoria_convites' AND INDEX_NAME = 'idx_convites_assessoria_status');
SET @sql = IF(@idx = 0, 'ALTER TABLE assessoria_convites ADD INDEX idx_convites_assessoria_status (assessoria_id, status)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'assessoria_convites' AND INDEX_NAME = 'idx_convites_atleta_status');
SET @sql = IF(@idx = 0, 'ALTER TABLE assessoria_convites ADD INDEX idx_convites_atleta_status (atleta_usuario_id, status)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'assessoria_convites' AND INDEX_NAME = 'idx_convites_expira_em');
SET @sql = IF(@idx = 0, 'ALTER TABLE assessoria_convites ADD INDEX idx_convites_expira_em (expira_em)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
