-- ============================================================
-- Script SQL para adicionar campo historico_corridas
-- MovAmazon - Migração de Anamnese
-- ============================================================
-- 
-- Este script adiciona o campo historico_corridas à tabela
-- anamneses se ele não existir.
--
-- Data: 2025-11-13
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Adicionar campo historico_corridas se não existir
-- --------------------------------------------------------

SET @dbname = DATABASE();
SET @tablename = 'anamneses';
SET @columnname = 'historico_corridas';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1 AS campo_ja_existe',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' text COLLATE utf8mb4_unicode_ci COMMENT ''Histórico de corridas do participante'' AFTER disponibilidade_horarios')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

