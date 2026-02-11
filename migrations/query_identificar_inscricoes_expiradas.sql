-- Query Direta: Identificar Inscrições Expiradas
-- Execute esta query para ver todas as inscrições que devem ser canceladas
-- Não requer tabelas temporárias - pode executar diretamente

-- Ver TODAS as inscrições que serão canceladas
SELECT 
    id,
    usuario_id,
    evento_id,
    data_inscricao,
    forma_pagamento,
    data_expiracao_pagamento,
    external_reference,
    preference_id,
    valor_total,
    TIMESTAMPDIFF(HOUR, data_inscricao, NOW()) as horas_pendente,
    CASE 
        WHEN forma_pagamento = 'boleto' 
             AND data_expiracao_pagamento IS NOT NULL 
             AND data_expiracao_pagamento < NOW() 
        THEN 'Boleto expirado'
        WHEN data_inscricao < DATE_SUB(NOW(), INTERVAL 72 HOUR) 
        THEN 'Pendente há mais de 72 horas'
        ELSE 'Outro'
    END as motivo
FROM inscricoes
WHERE status_pagamento = 'pendente'
  AND status = 'pendente'
  AND (
    -- Pendentes há mais de 72 horas
    data_inscricao < DATE_SUB(NOW(), INTERVAL 72 HOUR)
    OR
    -- Boletos expirados
    (forma_pagamento = 'boleto' 
     AND data_expiracao_pagamento IS NOT NULL 
     AND data_expiracao_pagamento < NOW())
  )
ORDER BY data_inscricao;

