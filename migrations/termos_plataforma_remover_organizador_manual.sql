-- ============================================
-- VERSÃO MANUAL - Execute cada bloco SEPARADAMENTE
-- Se der erro 1091 "não se pode fazer DROP" ou "Confira se existe" -> IGNORE e passe ao próximo
-- ============================================

-- 1. Remover FK (pode falhar - ignore)
ALTER TABLE termos_eventos DROP FOREIGN KEY fk_termos_organizador;

-- 2. Remover índice único (pode falhar - ignore)
ALTER TABLE termos_eventos DROP INDEX unique_organizador_ativo;

-- 3. Remover índice (pode falhar - ignore)
ALTER TABLE termos_eventos DROP INDEX idx_organizador;

-- 4. Remover coluna (OBRIGATÓRIO - deve funcionar se organizador_id existe)
ALTER TABLE termos_eventos DROP COLUMN organizador_id;
