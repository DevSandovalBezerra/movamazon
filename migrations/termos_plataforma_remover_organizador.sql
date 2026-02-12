-- ============================================
-- TERMOS COMO REGRAS DA PLATAFORMA
-- Remove organizador_id de termos_eventos
-- Execute APENAS em ambientes que possuem a coluna organizador_id
-- ============================================
-- Se der erro "Unknown column 'organizador_id'", a coluna não existe - não execute.
-- Produção (sem organizador_id): NÃO execute este script.
-- ============================================
-- Usa procedure para ignorar erros ao remover FK/índices inexistentes.
-- ============================================

DROP PROCEDURE IF EXISTS sp_termos_remover_organizador;

DELIMITER //

CREATE PROCEDURE sp_termos_remover_organizador()
BEGIN
    DECLARE CONTINUE HANDLER FOR 1091, 1025, 1553
    BEGIN
        -- 1091: Can't DROP, check that column/key exists
        -- 1025: Error on rename (FK)
        -- 1553: Unknown key
        -- Ignora e continua
    END;

    ALTER TABLE termos_eventos DROP FOREIGN KEY fk_termos_organizador;
    ALTER TABLE termos_eventos DROP INDEX unique_organizador_ativo;
    ALTER TABLE termos_eventos DROP INDEX idx_organizador;
    ALTER TABLE termos_eventos DROP COLUMN organizador_id;
END //

DELIMITER ;

CALL sp_termos_remover_organizador();
DROP PROCEDURE IF EXISTS sp_termos_remover_organizador;
