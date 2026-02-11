-- Script para adicionar campo semana_numero na tabela treinos
-- Data: 2025-01-XX
-- Objetivo: Permitir identificação correta de múltiplas semanas de treino

ALTER TABLE `treinos` 
ADD COLUMN `semana_numero` INT(11) DEFAULT NULL 
COMMENT 'Número da semana do plano (1, 2, 3, etc.)' 
AFTER `dia_semana_id`;

-- Criar índice para melhorar performance de queries por semana
CREATE INDEX `idx_treinos_semana_numero` ON `treinos` (`semana_numero`);

-- Atualizar treinos existentes para semana 1 (assumindo que são da primeira semana)
UPDATE `treinos` 
SET `semana_numero` = 1 
WHERE `semana_numero` IS NULL;

