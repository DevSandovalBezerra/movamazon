-- Script para adicionar campo inscricao_id nas tabelas relacionadas a treinos
-- Executar este script no banco de dados movamazon

-- Adicionar campo inscricao_id na tabela anamneses
ALTER TABLE `anamneses` 
ADD COLUMN `inscricao_id` INT NULL DEFAULT NULL AFTER `usuario_id`,
ADD INDEX `idx_anamneses_inscricao` (`inscricao_id`);

-- Adicionar campo inscricao_id na tabela planos_treino_gerados
ALTER TABLE `planos_treino_gerados` 
ADD COLUMN `inscricao_id` INT NULL DEFAULT NULL AFTER `usuario_id`,
ADD INDEX `idx_planos_inscricao` (`inscricao_id`);

-- Adicionar foreign keys (opcional, mas recomendado)
-- ALTER TABLE `anamneses` 
-- ADD CONSTRAINT `fk_anamneses_inscricao` FOREIGN KEY (`inscricao_id`) REFERENCES `inscricoes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ALTER TABLE `planos_treino_gerados` 
-- ADD CONSTRAINT `fk_planos_inscricao` FOREIGN KEY (`inscricao_id`) REFERENCES `inscricoes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

