-- ============================================
-- ETAPA 5: REMOVER COLUNAS ANTIGAS
-- Migration: migrate_termos_to_organizador
-- ============================================
-- Execute este script SQL diretamente no MySQL (phpMyAdmin, MySQL Workbench, etc.)
-- Este script verifica e remove índices/colunas apenas se existirem

-- ============================================
-- PARTE 1: VERIFICAR O QUE EXISTE
-- ============================================
-- Execute estas queries primeiro para ver o que precisa ser removido:

-- Ver índices existentes:
SELECT INDEX_NAME 
FROM INFORMATION_SCHEMA.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'termos_eventos' 
AND INDEX_NAME IN ('evento_id', 'modalidade_id');

-- Ver colunas existentes:
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'termos_eventos' 
AND COLUMN_NAME IN ('evento_id', 'modalidade_id');

-- ============================================
-- PARTE 2: REMOVER ÍNDICES (se existirem)
-- ============================================
-- Execute apenas se os índices existirem (verifique com as queries acima)

-- Remover índice evento_id (se existir)
-- Se der erro "Unknown key", significa que não existe - pode ignorar
ALTER TABLE termos_eventos DROP INDEX evento_id;

-- Remover índice modalidade_id (se existir)
-- Se der erro "Unknown key", significa que não existe - pode ignorar
ALTER TABLE termos_eventos DROP INDEX modalidade_id;

-- ============================================
-- PARTE 3: REMOVER COLUNAS (se existirem)
-- ============================================
-- Execute apenas se as colunas existirem (verifique com as queries acima)

-- Remover coluna evento_id (se existir)
-- Se der erro "Unknown column", significa que não existe - pode ignorar
ALTER TABLE termos_eventos DROP COLUMN evento_id;

-- Remover coluna modalidade_id (se existir)
-- Se der erro "Unknown column", significa que não existe - pode ignorar
ALTER TABLE termos_eventos DROP COLUMN modalidade_id;

-- ============================================
-- VERIFICAÇÃO FINAL
-- ============================================
-- Execute para confirmar que as colunas foram removidas:
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'termos_eventos' 
AND COLUMN_NAME IN ('evento_id', 'modalidade_id');
-- Resultado esperado: 0 linhas
