-- Migration: Limpeza de Inscrições Expiradas e Dados Órfãos
-- Data: 2026-01-01
-- Descrição: Cancela inscrições pendentes há mais de 72 horas, boletos expirados
--            e remove dados órfãos de tabelas relacionadas.
-- IMPORTANTE: Executar backup antes de executar este script!

SET @dbname = DATABASE();
SET @executado = 0;

-- ============================================
-- FASE 1: ANÁLISE E IDENTIFICAÇÃO
-- ============================================

-- Criar tabela temporária para armazenar IDs que serão cancelados
DROP TEMPORARY TABLE IF EXISTS inscricoes_a_cancelar;
CREATE TEMPORARY TABLE inscricoes_a_cancelar (
    id INT PRIMARY KEY,
    motivo VARCHAR(255),
    data_inscricao DATETIME,
    horas_pendente INT
);

-- 1.1. Identificar inscrições pendentes há mais de 72 horas
-- NOTA: Se receber erro sobre tabela temporária, execute primeiro as linhas 14-21 para criar a tabela
INSERT INTO inscricoes_a_cancelar (id, motivo, data_inscricao, horas_pendente)
SELECT 
    id,
    'Pendente há mais de 72 horas' as motivo,
    data_inscricao,
    TIMESTAMPDIFF(HOUR, data_inscricao, NOW()) as horas_pendente
FROM inscricoes
WHERE status_pagamento = 'pendente'
  AND status = 'pendente'
  AND data_inscricao < DATE_SUB(NOW(), INTERVAL 72 HOUR)
  AND id NOT IN (SELECT id FROM inscricoes_a_cancelar WHERE id IS NOT NULL);

-- 1.2. Identificar boletos expirados
INSERT INTO inscricoes_a_cancelar (id, motivo, data_inscricao, horas_pendente)
SELECT 
    id,
    'Boleto expirado' as motivo,
    data_inscricao,
    TIMESTAMPDIFF(HOUR, data_expiracao_pagamento, NOW()) as horas_pendente
FROM inscricoes
WHERE status_pagamento = 'pendente'
  AND forma_pagamento = 'boleto'
  AND data_expiracao_pagamento IS NOT NULL
  AND data_expiracao_pagamento < NOW()
  AND id NOT IN (SELECT id FROM inscricoes_a_cancelar WHERE id IS NOT NULL);

-- ============================================
-- FASE 2: RELATÓRIO ANTES DA LIMPEZA
-- ============================================

SELECT 
    '=== RELATÓRIO DE INSCRIÇÕES A CANCELAR ===' as relatorio,
    COUNT(*) as total_inscricoes,
    SUM(CASE WHEN motivo = 'Pendente há mais de 72 horas' THEN 1 ELSE 0 END) as pendentes_72h,
    SUM(CASE WHEN motivo = 'Boleto expirado' THEN 1 ELSE 0 END) as boletos_expirados
FROM inscricoes_a_cancelar;

SELECT 
    id,
    motivo,
    data_inscricao,
    horas_pendente
FROM inscricoes_a_cancelar
ORDER BY data_inscricao;

-- ============================================
-- FASE 3: LIMPEZA (EXECUTAR APENAS APÓS VALIDAÇÃO)
-- ============================================

-- IMPORTANTE: Descomente as linhas abaixo apenas após validar o relatório acima!

/*
START TRANSACTION;

-- 3.1. Cancelar inscrições identificadas
UPDATE inscricoes i
INNER JOIN inscricoes_a_cancelar ic ON i.id = ic.id
SET i.status_pagamento = 'cancelado',
    i.status = 'cancelada'
WHERE i.status_pagamento = 'pendente'
  AND i.status = 'pendente';

SET @canceladas = ROW_COUNT();

-- 3.2. Limpar produtos extras órfãos (de inscrições canceladas)
DELETE ipe
FROM inscricoes_produtos_extras ipe
INNER JOIN inscricoes_a_cancelar ic ON ipe.inscricao_id = ic.id;

SET @produtos_extras_removidos = ROW_COUNT();

-- 3.3. Limpar aceites de termos órfãos (de inscrições canceladas)
DELETE at
FROM aceites_termos at
INNER JOIN inscricoes_a_cancelar ic ON at.inscricao_id = ic.id;

SET @aceites_removidos = ROW_COUNT();

-- 3.4. Limpar pagamentos ML órfãos (de inscrições canceladas)
-- NOTA: Não deletar, apenas marcar como cancelado para manter histórico
UPDATE pagamentos_ml pm
INNER JOIN inscricoes_a_cancelar ic ON pm.inscricao_id = ic.id
SET pm.status = 'cancelado'
WHERE pm.status = 'pendente';

SET @pagamentos_ml_atualizados = ROW_COUNT();

-- 3.5. Relatório final
SELECT 
    '=== LIMPEZA CONCLUÍDA ===' as relatorio,
    @canceladas as inscricoes_canceladas,
    @produtos_extras_removidos as produtos_extras_removidos,
    @aceites_removidos as aceites_removidos,
    @pagamentos_ml_atualizados as pagamentos_ml_atualizados;

-- Validar antes de fazer COMMIT
-- Se tudo estiver correto, execute: COMMIT;
-- Se houver problemas, execute: ROLLBACK;

-- COMMIT;
-- ROLLBACK;
*/

-- ============================================
-- FASE 4: VERIFICAÇÃO DE DADOS ÓRFÃOS
-- ============================================

-- 4.1. Verificar produtos extras órfãos (inscrições que não existem mais)
SELECT 
    'Produtos Extras Órfãos' as tipo,
    COUNT(*) as total
FROM inscricoes_produtos_extras ipe
LEFT JOIN inscricoes i ON ipe.inscricao_id = i.id
WHERE i.id IS NULL;

-- 4.2. Verificar aceites de termos órfãos
SELECT 
    'Aceites de Termos Órfãos' as tipo,
    COUNT(*) as total
FROM aceites_termos at
LEFT JOIN inscricoes i ON at.inscricao_id = i.id
WHERE i.id IS NULL;

-- 4.3. Verificar pagamentos ML órfãos
SELECT 
    'Pagamentos ML Órfãos' as tipo,
    COUNT(*) as total
FROM pagamentos_ml pm
LEFT JOIN inscricoes i ON pm.inscricao_id = i.id
WHERE i.id IS NULL;

-- ============================================
-- FASE 5: LIMPEZA DE DADOS ÓRFÃOS (OPCIONAL)
-- ============================================

-- IMPORTANTE: Descomente apenas se houver dados órfãos identificados na Fase 4!

/*
START TRANSACTION;

-- 5.1. Remover produtos extras órfãos
DELETE ipe
FROM inscricoes_produtos_extras ipe
LEFT JOIN inscricoes i ON ipe.inscricao_id = i.id
WHERE i.id IS NULL;

SET @produtos_extras_orfaos = ROW_COUNT();

-- 5.2. Remover aceites de termos órfãos
DELETE at
FROM aceites_termos at
LEFT JOIN inscricoes i ON at.inscricao_id = i.id
WHERE i.id IS NULL;

SET @aceites_orfaos = ROW_COUNT();

-- 5.3. Atualizar pagamentos ML órfãos (não deletar, apenas marcar)
UPDATE pagamentos_ml pm
LEFT JOIN inscricoes i ON pm.inscricao_id = i.id
SET pm.status = 'cancelado'
WHERE i.id IS NULL AND pm.status = 'pendente';

SET @pagamentos_ml_orfaos = ROW_COUNT();

SELECT 
    '=== DADOS ÓRFÃOS REMOVIDOS ===' as relatorio,
    @produtos_extras_orfaos as produtos_extras_orfaos,
    @aceites_orfaos as aceites_orfaos,
    @pagamentos_ml_orfaos as pagamentos_ml_orfaos;

-- COMMIT;
-- ROLLBACK;
*/

-- Limpar tabela temporária
DROP TEMPORARY TABLE IF EXISTS inscricoes_a_cancelar;

SELECT 'Script de análise concluído. Revise os relatórios antes de executar a limpeza.' as mensagem;

