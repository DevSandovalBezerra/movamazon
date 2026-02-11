-- ============================================================
-- Script SQL para criar a tabela anamneses
-- MovAmazon - Sistema de Anamnese
-- ============================================================
-- 
-- Este script cria a tabela anamneses se ela não existir,
-- ou adiciona o campo inscricao_id se a tabela já existir
-- mas não tiver esse campo.
--
-- Data: 2025-11-13
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Criar tabela anamneses se não existir
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `anamneses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `inscricao_id` int(11) DEFAULT NULL COMMENT 'Vinculação com inscrição na corrida',
  `profissional_id` int(11) DEFAULT NULL,
  `data_anamnese` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `peso` decimal(5,2) NOT NULL,
  `altura` int(11) NOT NULL,
  `imc` decimal(4,2) DEFAULT NULL,
  `fc_maxima` smallint(6) DEFAULT NULL COMMENT 'Frequência Cardíaca Máxima (bpm)',
  `vo2_max` decimal(5,2) DEFAULT NULL COMMENT 'VO2 Máximo (ml/kg/min)',
  `zona_alvo_treino` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Zona Alvo de Treinamento (descritivo ou faixa)',
  `doencas_preexistentes` text COLLATE utf8mb4_unicode_ci,
  `uso_medicamentos` text COLLATE utf8mb4_unicode_ci,
  `nivel_atividade` enum('ativo','inativo') COLLATE utf8mb4_unicode_ci NOT NULL,
  `objetivo_principal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foco_primario` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_glicemia` int(11) DEFAULT NULL COMMENT 'Glicemia máxima (mg/dL) - opcional para anamnese simplificada',
  `limitacoes_fisicas` text COLLATE utf8mb4_unicode_ci,
  `preferencias_atividades` text COLLATE utf8mb4_unicode_ci,
  `disponibilidade_horarios` text COLLATE utf8mb4_unicode_ci,
  `historico_corridas` text COLLATE utf8mb4_unicode_ci COMMENT 'Histórico de corridas do participante',
  `assinatura_aluno` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assinatura_responsavel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pendente','em_analise','aprovada','arquivada') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  PRIMARY KEY (`id`),
  KEY `idx_anamneses_usuario` (`usuario_id`),
  KEY `idx_anamneses_profissional` (`profissional_id`),
  KEY `idx_anamneses_inscricao` (`inscricao_id`),
  KEY `idx_usuario` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Adicionar campo inscricao_id se a tabela já existir
-- mas não tiver esse campo
-- --------------------------------------------------------

SET @dbname = DATABASE();
SET @tablename = 'anamneses';
SET @columnname = 'inscricao_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' int(11) DEFAULT NULL COMMENT ''Vinculação com inscrição na corrida'', ADD KEY idx_anamneses_inscricao (', @columnname, ')')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

