-- ========================================
-- ADICIONAR CONFIGURAÇÃO TREINO
-- Execute este comando AGORA
-- ========================================

INSERT INTO `config` (`chave`, `valor`, `tipo`, `categoria`, `descricao`, `editavel`, `visivel`, `validacao`)
VALUES (
  'treino.exigir_inscricao',
  'true',
  'boolean',
  'treino',
  'Exigir inscrição confirmada para gerar treino (desativar apenas temporariamente)',
  1,
  1,
  NULL
);

-- Verificar se foi inserido
SELECT * FROM `config` WHERE `chave` = 'treino.exigir_inscricao';