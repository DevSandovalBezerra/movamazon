-- Migration aditiva e segura (sem UNIQUE em inscricao_id)
-- Objetivo: compatibilizar periodizacao/termos no plano de treino sem downtime.

-- bibliografia_json
SET @sql := IF(
  EXISTS(
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'planos_treino_gerados'
      AND COLUMN_NAME = 'bibliografia_json'
  ),
  'SELECT ''coluna bibliografia_json ja existe''',
  'ALTER TABLE planos_treino_gerados ADD COLUMN bibliografia_json LONGTEXT NULL AFTER bibliografia_plano'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- aceite_termos_treino
SET @sql := IF(
  EXISTS(
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'planos_treino_gerados'
      AND COLUMN_NAME = 'aceite_termos_treino'
  ),
  'SELECT ''coluna aceite_termos_treino ja existe''',
  'ALTER TABLE planos_treino_gerados ADD COLUMN aceite_termos_treino TINYINT(1) NULL DEFAULT NULL AFTER equipamento_geral'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- data_aceite_termos_treino
SET @sql := IF(
  EXISTS(
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'planos_treino_gerados'
      AND COLUMN_NAME = 'data_aceite_termos_treino'
  ),
  'SELECT ''coluna data_aceite_termos_treino ja existe''',
  'ALTER TABLE planos_treino_gerados ADD COLUMN data_aceite_termos_treino DATETIME NULL AFTER aceite_termos_treino'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- termos_id_treino
SET @sql := IF(
  EXISTS(
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'planos_treino_gerados'
      AND COLUMN_NAME = 'termos_id_treino'
  ),
  'SELECT ''coluna termos_id_treino ja existe''',
  'ALTER TABLE planos_treino_gerados ADD COLUMN termos_id_treino INT NULL AFTER data_aceite_termos_treino'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- periodizacao_json
SET @sql := IF(
  EXISTS(
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'planos_treino_gerados'
      AND COLUMN_NAME = 'periodizacao_json'
  ),
  'SELECT ''coluna periodizacao_json ja existe''',
  'ALTER TABLE planos_treino_gerados ADD COLUMN periodizacao_json LONGTEXT NULL AFTER termos_id_treino'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- schema_version
SET @sql := IF(
  EXISTS(
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'planos_treino_gerados'
      AND COLUMN_NAME = 'schema_version'
  ),
  'SELECT ''coluna schema_version ja existe''',
  'ALTER TABLE planos_treino_gerados ADD COLUMN schema_version VARCHAR(20) NULL AFTER periodizacao_json'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- metodologia
SET @sql := IF(
  EXISTS(
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'planos_treino_gerados'
      AND COLUMN_NAME = 'metodologia'
  ),
  'SELECT ''coluna metodologia ja existe''',
  'ALTER TABLE planos_treino_gerados ADD COLUMN metodologia VARCHAR(50) NULL AFTER schema_version'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- indice composto para leitura por inscricao mais recente
SET @sql := IF(
  EXISTS(
    SELECT 1
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'planos_treino_gerados'
      AND INDEX_NAME = 'idx_planos_inscricao_data'
  ),
  'SELECT ''indice idx_planos_inscricao_data ja existe''',
  'ALTER TABLE planos_treino_gerados ADD INDEX idx_planos_inscricao_data (inscricao_id, data_criacao_plano)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- indice composto para leitura por usuario mais recente
SET @sql := IF(
  EXISTS(
    SELECT 1
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'planos_treino_gerados'
      AND INDEX_NAME = 'idx_planos_usuario_data'
  ),
  'SELECT ''indice idx_planos_usuario_data ja existe''',
  'ALTER TABLE planos_treino_gerados ADD INDEX idx_planos_usuario_data (usuario_id, data_criacao_plano)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
