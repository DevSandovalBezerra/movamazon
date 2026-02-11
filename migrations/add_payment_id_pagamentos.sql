-- Migration: adicionar payment_id na tabela pagamentos (IDEMPOTENTE)
-- Objetivo: permitir idempotencia por transacao MP (evitar duplicatas e reprocessamento seguro).
-- O webhook preenche payment_id ao processar notificacoes; registros antigos permanecem com payment_id NULL.
-- Pode ser executada mais de uma vez: se a coluna ou o indice ja existirem, nada e alterado.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

DELIMITER //

DROP PROCEDURE IF EXISTS add_payment_id_pagamentos_once//

CREATE PROCEDURE add_payment_id_pagamentos_once()
BEGIN
  -- So adiciona a coluna se ainda nao existir
  IF (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pagamentos' AND COLUMN_NAME = 'payment_id') = 0 THEN
    ALTER TABLE pagamentos
      ADD COLUMN payment_id VARCHAR(100) NULL DEFAULT NULL COMMENT 'ID da transacao no Mercado Pago' AFTER status;
  END IF;

  -- So cria o indice se ainda nao existir
  IF (SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pagamentos' AND INDEX_NAME = 'idx_payment_id') = 0 THEN
    CREATE UNIQUE INDEX idx_payment_id ON pagamentos (payment_id);
  END IF;
END//

DELIMITER ;

CALL add_payment_id_pagamentos_once();
DROP PROCEDURE IF EXISTS add_payment_id_pagamentos_once;
