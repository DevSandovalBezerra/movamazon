-- Script para criar inscrição para o usuário 5 (Melvin Marble)
-- Evento: III CORRIDA MICO LEAO (ID: 3)
-- Modalidade: CORRIDA 10KM (ID: 19)

-- Gerar número de inscrição único baseado no timestamp
SET @numero_inscricao = CONCAT('MOV', YEAR(NOW()), LPAD(MONTH(NOW()), 2, '0'), LPAD(DAY(NOW()), 2, '0'), '-', LPAD((SELECT COALESCE(MAX(id), 0) + 1 FROM inscricoes), 4, '0'));
SET @external_ref = CONCAT('MOVAMAZONAS_', UNIX_TIMESTAMP(), '_5');
SET @protocolo = CONCAT('PROT-', YEAR(NOW()), LPAD(MONTH(NOW()), 2, '0'), LPAD(DAY(NOW()), 2, '0'), '-', LPAD((SELECT COALESCE(MAX(id), 0) + 1 FROM inscricoes), 5, '0'));

-- Inserir inscrição
INSERT INTO `inscricoes` (
    `usuario_id`,
    `evento_id`,
    `modalidade_evento_id`,
    `lote_inscricao_id`,
    `tipo_publico`,
    `kit_modalidade_id`,
    `kit_id`,
    `tamanho_camiseta`,
    `tamanho_id`,
    `produtos_extras_ids`,
    `numero_inscricao`,
    `protocolo`,
    `grupo_assessoria`,
    `nome_equipe`,
    `ordem_equipe`,
    `posicao_legenda`,
    `escolha_tamanho`,
    `fisicamente_apto`,
    `apelido_peito`,
    `contato_emergencia_nome`,
    `contato_emergencia_telefone`,
    `equipe_extra`,
    `doc_comprovante_universidade`,
    `data_inscricao`,
    `status`,
    `status_pagamento`,
    `valor_total`,
    `valor_desconto`,
    `cupom_aplicado`,
    `data_pagamento`,
    `forma_pagamento`,
    `parcelas`,
    `seguro_contratado`,
    `external_reference`,
    `preference_id`,
    `colocacao`,
    `aceite_termos`,
    `data_aceite_termos`,
    `versao_termos`
) VALUES (
    5,                              -- usuario_id (Melvin Marble)
    3,                              -- evento_id (III CORRIDA MICO LEAO)
    19,                             -- modalidade_evento_id (CORRIDA 10KM)
    NULL,                           -- lote_inscricao_id (sem lote cadastrado para evento 3)
    'publico_geral',                -- tipo_publico
    NULL,                           -- kit_modalidade_id
    NULL,                           -- kit_id
    'G',                            -- tamanho_camiseta
    NULL,                           -- tamanho_id
    NULL,                           -- produtos_extras_ids
    @numero_inscricao,              -- numero_inscricao (gerado automaticamente)
    @protocolo,                     -- protocolo (gerado automaticamente)
    NULL,                           -- grupo_assessoria
    NULL,                           -- nome_equipe
    NULL,                           -- ordem_equipe
    NULL,                           -- posicao_legenda
    NULL,                           -- escolha_tamanho
    1,                              -- fisicamente_apto (sim)
    NULL,                           -- apelido_peito
    'Contato Emergência',           -- contato_emergencia_nome
    '92999999999',                  -- contato_emergencia_telefone
    NULL,                           -- equipe_extra
    NULL,                           -- doc_comprovante_universidade
    NOW(),                          -- data_inscricao
    'pendente',                     -- status
    'pendente',                     -- status_pagamento
    145.00,                         -- valor_total (valor padrão para público geral)
    0.00,                           -- valor_desconto
    NULL,                           -- cupom_aplicado
    NULL,                           -- data_pagamento
    NULL,                           -- forma_pagamento
    1,                              -- parcelas
    0,                              -- seguro_contratado
    @external_ref,                  -- external_reference (gerado automaticamente)
    NULL,                           -- preference_id (será preenchido ao criar preferência de pagamento)
    NULL,                           -- colocacao
    1,                              -- aceite_termos (aceito)
    NOW(),                          -- data_aceite_termos
    '1.0'                           -- versao_termos
);

-- Exibir informações da inscrição criada
SELECT 
    i.id AS inscricao_id,
    i.numero_inscricao,
    i.protocolo,
    u.nome_completo AS participante,
    e.nome AS evento,
    m.nome AS modalidade,
    i.status,
    i.status_pagamento,
    i.valor_total,
    i.data_inscricao
FROM inscricoes i
INNER JOIN usuarios u ON i.usuario_id = u.id
INNER JOIN eventos e ON i.evento_id = e.id
INNER JOIN modalidades m ON i.modalidade_evento_id = m.id
WHERE i.usuario_id = 5 
  AND i.evento_id = 3
ORDER BY i.id DESC
LIMIT 1;

