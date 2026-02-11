-- Script para adicionar campo foto_perfil na tabela usuarios
-- Data: 2025-11-17

ALTER TABLE `usuarios` 
ADD COLUMN `foto_perfil` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL 
COMMENT 'Caminho/nome do arquivo da foto de perfil do usuário' 
AFTER `pais`;

