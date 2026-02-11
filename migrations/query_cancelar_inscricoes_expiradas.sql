-- Query Direta: Cancelar Inscrições Expiradas
-- IMPORTANTE: Execute primeiro a query de identificação para ver o que será cancelado!
-- Execute esta query APENAS após validar os dados

-- ============================================
-- CANCELAR INSCRIÇÕES EXPIRADAS
-- ============================================

START TRANSACTION;

-- 1. Cancelar inscrições pendentes há mais de 72 horas
UPDATE inscricoes
SET status_pagamento = 'cancelado',
    status = 'cancelada'
WHERE status_pagamento = 'pendente'
  AND status = 'pendente'
  AND data_inscricao < DATE_SUB(NOW(), INTERVAL 72 HOUR);

-- 2. Cancelar boletos expirados
UPDATE inscricoes
SET status_pagamento = 'cancelado',
    status = 'cancelada'
WHERE status_pagamento = 'pendente'
  AND forma_pagamento = 'boleto'
  AND data_expiracao_pagamento IS NOT NULL
  AND data_expiracao_pagamento < NOW();

-- 3. Verificar quantas foram canceladas
SELECT 
    'Inscrições canceladas' as acao,
    COUNT(*) as total
FROM inscricoes
WHERE status_pagamento = 'cancelado'
  AND status = 'cancelada'
  AND (
    data_inscricao < DATE_SUB(NOW(), INTERVAL 72 HOUR)
    OR
    (forma_pagamento = 'boleto' 
     AND data_expiracao_pagamento IS NOT NULL 
     AND data_expiracao_pagamento < NOW())
  );

-- IMPORTANTE: Revise os resultados acima antes de fazer COMMIT!
-- Se estiver tudo correto, execute: COMMIT;
-- Se houver problemas, execute: ROLLBACK;

-- COMMIT;
-- ROLLBACK;

