-- ============================================================
-- Script SQL completo para criação de todas as tabelas
-- necessárias para o sistema de anamnese e geração de treinos
-- de corrida - MovAmazon
-- ============================================================
-- 
-- Este script cria as seguintes tabelas:
-- 1. anamneses - Armazena anamneses dos participantes
-- 2. planos_treino_gerados - Armazena planos de treino gerados
-- 3. treinos - Armazena treinos individuais (dias da semana)
-- 4. treino_exercicios - Armazena exercícios de cada treino
-- 5. progresso_treino - Armazena progresso/execução dos treinos
-- 6. openai_token_usage - Armazena uso de tokens da API OpenAI
--
-- O script também adiciona o campo inscricao_id nas tabelas
-- anamneses e planos_treino_gerados se as tabelas já existirem.
--
-- Data: 2025-11-06
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- 1. Tabela: anamneses
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
-- 2. Tabela: planos_treino_gerados
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `planos_treino_gerados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `inscricao_id` int(11) DEFAULT NULL COMMENT 'Vinculação com inscrição na corrida',
  `profissional_id` int(11) DEFAULT NULL,
  `anamnese_id` int(11) NOT NULL,
  `data_criacao_plano` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `bibliografia_plano` text COLLATE utf8mb4_unicode_ci,
  `foco_primario` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duracao_treino_geral` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dias_plano` int(11) DEFAULT '5' COMMENT 'Duração do plano em dias',
  `equipamento_geral` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_planos_usuario` (`usuario_id`),
  KEY `idx_planos_profissional` (`profissional_id`),
  KEY `idx_planos_anamnese` (`anamnese_id`),
  KEY `idx_planos_inscricao` (`inscricao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Tabela: treinos
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `treinos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anamnese_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `plano_treino_gerado_id` int(11) NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `nivel_dificuldade` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'intermediario',
  `dia_semana_id` int(11) DEFAULT NULL COMMENT '1=Domingo, 2=Segunda, etc.',
  `parte_inicial` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON com exercícios de aquecimento',
  `parte_principal` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON com exercícios principais',
  `volta_calma` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON com exercícios de volta à calma',
  `fcmax` int(11) DEFAULT NULL COMMENT 'Frequência cardíaca máxima',
  `volume_total` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grupos_musculares` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_series` int(11) DEFAULT '3',
  `intervalo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_repeticoes` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `intensidade` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `carga_interna` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ativo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_treinos_anamnese` (`anamnese_id`),
  KEY `idx_treinos_usuario` (`usuario_id`),
  KEY `idx_treinos_plano` (`plano_treino_gerado_id`),
  KEY `idx_treinos_dia_semana` (`dia_semana_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Tabela: treino_exercicios
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `treino_exercicios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `treino_id` int(11) NOT NULL,
  `nome_exercicio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exercicio_id` int(11) DEFAULT NULL,
  `series` int(11) DEFAULT NULL,
  `repeticoes` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tempo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tempo de duração do exercício',
  `peso` decimal(10,2) DEFAULT NULL,
  `tempo_descanso` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON com dados completos do exercício',
  `tipo` enum('repeticao','tempo','livre') COLLATE utf8mb4_unicode_ci DEFAULT 'livre',
  PRIMARY KEY (`id`),
  KEY `idx_treino_exercicios_treino` (`treino_id`),
  KEY `idx_treino_exercicios_exercicio` (`exercicio_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Tabela: progresso_treino
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `progresso_treino` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `treino_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `data_realizado` date NOT NULL,
  `percepcao_esforco` int(11) DEFAULT NULL COMMENT 'PSE de 0 a 10 (recomendado 3-5 para diabetes)',
  `duracao_minutos` int(11) DEFAULT NULL,
  `glicemia_pre_treino` int(11) DEFAULT NULL COMMENT 'Glicemia antes do treino (mg/dL)',
  `glicemia_pos_treino` int(11) DEFAULT NULL COMMENT 'Glicemia após o treino (mg/dL)',
  `sinais_alerta_observados` text COLLATE utf8mb4_unicode_ci COMMENT 'Tontura, sudorese fria, tremores, fraqueza, visão turva, confusão mental',
  `mal_estar_observado` enum('sim','nao') COLLATE utf8mb4_unicode_ci DEFAULT 'nao' COMMENT 'Houve mal-estar durante ou após o treino',
  `observacoes` text COLLATE utf8mb4_unicode_ci COMMENT 'Alterações glicêmicas, desconfortos anormais e outras observações',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_progresso_treino` (`treino_id`,`data_realizado`),
  KEY `idx_glicemia_monitoring` (`glicemia_pre_treino`,`glicemia_pos_treino`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. Tabela: openai_token_usage
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `openai_token_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_id` int(11) DEFAULT NULL,
  `endpoint` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modelo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prompt_tokens` int(11) DEFAULT '0',
  `completion_tokens` int(11) DEFAULT '0',
  `total_tokens` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_openai_usuario` (`usuario_id`),
  KEY `idx_openai_data` (`data_hora`),
  KEY `idx_openai_endpoint` (`endpoint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 7. Adicionar campos inscricao_id se as tabelas já existirem
-- --------------------------------------------------------

-- Verificar e adicionar inscricao_id em anamneses se não existir
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'anamneses' 
    AND COLUMN_NAME = 'inscricao_id');
    
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `anamneses` ADD COLUMN `inscricao_id` INT(11) NULL DEFAULT NULL AFTER `usuario_id`, ADD INDEX `idx_anamneses_inscricao` (`inscricao_id`);',
    'SELECT "Campo inscricao_id já existe em anamneses" AS mensagem;');
    
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar e adicionar inscricao_id em planos_treino_gerados se não existir
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'planos_treino_gerados' 
    AND COLUMN_NAME = 'inscricao_id');
    
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `planos_treino_gerados` ADD COLUMN `inscricao_id` INT(11) NULL DEFAULT NULL AFTER `usuario_id`, ADD INDEX `idx_planos_inscricao` (`inscricao_id`);',
    'SELECT "Campo inscricao_id já existe em planos_treino_gerados" AS mensagem;');
    
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- 8. Foreign Keys (opcional - descomente se desejar)
-- --------------------------------------------------------

-- ALTER TABLE `anamneses` 
--   ADD CONSTRAINT `fk_anamneses_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
--   ADD CONSTRAINT `fk_anamneses_inscricao` FOREIGN KEY (`inscricao_id`) REFERENCES `inscricoes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ALTER TABLE `planos_treino_gerados` 
--   ADD CONSTRAINT `fk_planos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
--   ADD CONSTRAINT `fk_planos_anamnese` FOREIGN KEY (`anamnese_id`) REFERENCES `anamneses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
--   ADD CONSTRAINT `fk_planos_inscricao` FOREIGN KEY (`inscricao_id`) REFERENCES `inscricoes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ALTER TABLE `treinos` 
--   ADD CONSTRAINT `fk_treinos_anamnese` FOREIGN KEY (`anamnese_id`) REFERENCES `anamneses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
--   ADD CONSTRAINT `fk_treinos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
--   ADD CONSTRAINT `fk_treinos_plano` FOREIGN KEY (`plano_treino_gerado_id`) REFERENCES `planos_treino_gerados` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ALTER TABLE `treino_exercicios` 
--   ADD CONSTRAINT `fk_treino_exercicios_treino` FOREIGN KEY (`treino_id`) REFERENCES `treinos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ALTER TABLE `progresso_treino` 
--   ADD CONSTRAINT `fk_progresso_treino` FOREIGN KEY (`treino_id`) REFERENCES `treinos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
--   ADD CONSTRAINT `fk_progresso_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;

