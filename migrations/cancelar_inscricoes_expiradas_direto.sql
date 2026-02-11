-- Cancelar Inscrições Expiradas - Execução Direta
-- Esta query cancela automaticamente todas as inscrições expiradas
-- Baseado nas regras: 72 horas pendentes OU boletos expirados
-- IMPORTANTE: Execute backup antes!

START TRANSACTION;

-- Cancelar todas as inscrições expiradas de uma vez
UPDATE inscricoes
SET status_pagamento = 'cancelado',
    status = 'cancelada'
WHERE status_pagamento = 'pendente'
  AND status = 'pendente'
  AND (
    -- Regra 1: Pendentes há mais de 72 horas
    data_inscricao < DATE_SUB(NOW(), INTERVAL 72 HOUR)
    OR
    -- Regra 2: Boletos expirados
    (forma_pagamento = 'boleto' 
     AND data_expiracao_pagamento IS NOT NULL 
     AND data_expiracao_pagamento < NOW())
  );

-- Ver quantas foram canceladas
SELECT 
    ROW_COUNT() as inscricoes_canceladas,
    'Inscrições canceladas com sucesso' as mensagem;

-- IMPORTANTE: Se estiver tudo correto, execute: COMMIT;
-- Se houver problemas, execute: ROLLBACK;

-- COMMIT;
-- ROLLBACK;

