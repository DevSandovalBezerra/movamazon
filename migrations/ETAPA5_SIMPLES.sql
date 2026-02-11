-- ============================================
-- ETAPA 5: REMOVER COLUNAS ANTIGAS - VERSÃO SIMPLES
-- ============================================
-- Execute este arquivo diretamente no phpMyAdmin ou MySQL
-- Se algum comando der erro "Unknown key" ou "Unknown column", IGNORE (significa que não existe)

-- Remover índices (pode dar erro se não existirem - ignore)
ALTER TABLE termos_eventos DROP INDEX evento_id;
ALTER TABLE termos_eventos DROP INDEX modalidade_id;

-- Remover colunas (pode dar erro se não existirem - ignore)
ALTER TABLE termos_eventos DROP COLUMN evento_id;
ALTER TABLE termos_eventos DROP COLUMN modalidade_id;
