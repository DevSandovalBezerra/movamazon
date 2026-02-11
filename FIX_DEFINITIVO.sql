-- ============================================
-- SOLUÇÃO DEFINITIVA - REMOVER CONSTRAINT REAL
-- Execute NO PHPMYADMIN agora
-- ============================================

-- Este é o comando EXATO que vai funcionar:
ALTER TABLE `config_historico` 
DROP FOREIGN KEY `fk_config_historico_admin`;

-- PRONTO! Só isso resolve.