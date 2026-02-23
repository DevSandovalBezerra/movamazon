INSERT INTO assessoria_atletas (assessoria_id, atleta_usuario_id, assessor_usuario_id, origem, data_inicio, status)
SELECT
  :assessoria_id,
  i.usuario_id,
  NULL,
  'inscricao_evento',
  CURDATE(),
  'ativo'
FROM inscricoes i
WHERE i.evento_id = :evento_id
  AND i.status_pagamento IN ('approved','pago','paid','confirmado','aprovado')
  AND NOT EXISTS (
    SELECT 1
    FROM assessoria_atletas aa
    WHERE aa.assessoria_id = :assessoria_id
      AND aa.atleta_usuario_id = i.usuario_id
  );