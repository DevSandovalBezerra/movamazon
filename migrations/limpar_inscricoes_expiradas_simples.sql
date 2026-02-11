-- Migration: Limpeza de Inscrições Expiradas (Versão Simplificada)
-- Data: 2026-01-01
-- Descrição: Versão simplificada sem tabelas temporárias para execução direta
-- IMPORTANTE: Executar backup antes de executar este script!

-- ============================================
-- FASE 1: ANÁLISE E IDENTIFICAÇÃO
-- ============================================

-- 1.1. Ver inscrições pendentes há mais de 72 horas
SELECT 
    id,
    usuario_id,
    evento_id,
    data_inscricao,
    forma_pagamento,
    data_expiracao_pagamento,
    external_reference,
    TIMESTAMPDIFF(HOUR, data_inscricao, NOW()) as horas_pendente,
    'Pendente há mais de 72 horas' as motivo
FROM inscricoes
WHERE status_pagamento = 'pendente'
  AND status = 'pendente'
  AND data_inscricao < DATE_SUB(NOW(), INTERVAL 72 HOUR)
ORDER BY data_inscricao;

-- 1.2. Ver boletos expirados
SELECT 
    id,
    usuario_id,
    evento_id,
    data_inscricao,
    forma_pagamento,
    data_expiracao_pagamento,
    external_reference,
    TIMESTAMPDIFF(HOUR, data_expiracao_pagamento, NOW()) as horas_expirado,
    'Boleto expirado' as motivo
FROM inscricoes
WHERE status_pagamento = 'pendente'
  AND forma_pagamento = 'boleto'
  AND data_expiracao_pagamento IS NOT NULL
  AND data_expiracao_pagamento < NOW()
ORDER BY data_expiracao_pagamento;

-- 1.3. Ver TODAS as inscrições que serão canceladas (combinação)
SELECT 
    id,
    usuario_id,
    evento_id,
    data_inscricao,
    forma_pagamento,
    data_expiracao_pagamento,
    external_reference,
    TIMESTAMPDIFF(HOUR, data_inscricao, NOW()) as horas_pendente,
    CASE 
        WHEN data_expiracao_pagamento IS NOT NULL AND data_expiracao_pagamento < NOW() THEN 'Boleto expirado'
        WHEN data_inscricao < DATE_SUB(NOW(), INTERVAL 72 HOUR) THEN 'Pendente há mais de 72 horas'
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

-- 1.4. Contagem total
SELECT 
    '=== RESUMO ===' as relatorio,
    COUNT(*) as total_inscricoes_a_cancelar,
    SUM(CASE WHEN data_inscricao < DATE_SUB(NOW(), INTERVAL 72 HOUR) THEN 1 ELSE 0 END) as pendentes_72h,
    SUM(CASE WHEN forma_pagamento = 'boleto' AND data_expiracao_pagamento IS NOT NULL AND data_expiracao_pagamento < NOW() THEN 1 ELSE 0 END) as boletos_expirados
FROM inscricoes
WHERE status_pagamento = 'pendente'
  AND status = 'pendente'
  AND (
    data_inscricao < DATE_SUB(NOW(), INTERVAL 72 HOUR)
    OR
    (forma_pagamento = 'boleto' 
     AND data_expiracao_pagamento IS NOT NULL 
     AND data_expiracao_pagamento < NOW())
  );

-- ============================================
-- FASE 2: LIMPEZA (EXECUTAR APENAS APÓS VALIDAÇÃO)
-- ============================================

-- IMPORTANTE: Descomente as queries abaixo apenas após validar o relatório acima!

/*
START TRANSACTION;

-- 2.1. Cancelar inscrições pendentes há mais de 72 horas
UPDATE inscricoes
SET status_pagamento = 'cancelado',
    status = 'cancelada'
WHERE status_pagamento = 'pendente'
  AND status = 'pendente'
  AND data_inscricao < DATE_SUB(NOW(), INTERVAL 72 HOUR);

-- 2.2. Cancelar boletos expirados
UPDATE inscricoes
SET status_pagamento = 'cancelado',
    status = 'cancelada'
WHERE status_pagamento = 'pendente'
  AND forma_pagamento = 'boleto'
  AND data_expiracao_pagamento IS NOT NULL
  AND data_expiracao_pagamento < NOW();

-- 2.3. Limpar produtos extras de inscrições canceladas
DELETE ipe
FROM inscricoes_produtos_extras ipe
INNER JOIN inscricoes i ON ipe.inscricao_id = i.id
WHERE i.status = 'cancelada'
  AND i.status_pagamento = 'cancelado';

-- 2.4. Limpar aceites de termos de inscrições canceladas
DELETE at
FROM aceites_termos at
INNER JOIN inscricoes i ON at.inscricao_id = i.id
WHERE i.status = 'cancelada'
  AND i.status_pagamento = 'cancelado';

-- 2.5. Atualizar pagamentos ML de inscrições canceladas
UPDATE pagamentos_ml pm
INNER JOIN inscricoes i ON pm.inscricao_id = i.id
SET pm.status = 'cancelado'
WHERE i.status = 'cancelada'
  AND i.status_pagamento = 'cancelado'
  AND pm.status = 'pendente';

-- Validar antes de fazer COMMIT
-- Se tudo estiver correto, execute: COMMIT;
-- Se houver problemas, execute: ROLLBACK;

-- COMMIT;
-- ROLLBACK;
*/

-- ============================================
-- FASE 3: VERIFICAÇÃO DE DADOS ÓRFÃOS
-- ============================================

-- 3.1. Verificar produtos extras órfãos
SELECT 
    'Produtos Extras Órfãos' as tipo,
    COUNT(*) as total
FROM inscricoes_produtos_extras ipe
LEFT JOIN inscricoes i ON ipe.inscricao_id = i.id
WHERE i.id IS NULL;

-- 3.2. Verificar aceites de termos órfãos
SELECT 
    'Aceites de Termos Órfãos' as tipo,
    COUNT(*) as total
FROM aceites_termos at
LEFT JOIN inscricoes i ON at.inscricao_id = i.id
WHERE i.id IS NULL;

-- 3.3. Verificar pagamentos ML órfãos
SELECT 
    'Pagamentos ML Órfãos' as tipo,
    COUNT(*) as total
FROM pagamentos_ml pm
LEFT JOIN inscricoes i ON pm.inscricao_id = i.id
WHERE i.id IS NULL;

-- 3.4. Listar produtos extras órfãos (detalhes)
SELECT ipe.*
FROM inscricoes_produtos_extras ipe
LEFT JOIN inscricoes i ON ipe.inscricao_id = i.id
WHERE i.id IS NULL;

-- 3.5. Listar aceites de termos órfãos (detalhes)
SELECT at.*
FROM aceites_termos at
LEFT JOIN inscricoes i ON at.inscricao_id = i.id
WHERE i.id IS NULL;

-- 3.6. Listar pagamentos ML órfãos (detalhes)
SELECT pm.*
FROM pagamentos_ml pm
LEFT JOIN inscricoes i ON pm.inscricao_id = i.id
WHERE i.id IS NULL;

-- ============================================
-- FASE 4: LIMPEZA DE DADOS ÓRFÃOS (OPCIONAL)
-- ============================================

-- IMPORTANTE: Descomente apenas se houver dados órfãos identificados na Fase 3!

/*
START TRANSACTION;

-- 4.1. Remover produtos extras órfãos
DELETE ipe
FROM inscricoes_produtos_extras ipe
LEFT JOIN inscricoes i ON ipe.inscricao_id = i.id
WHERE i.id IS NULL;

-- 4.2. Remover aceites de termos órfãos
DELETE at
FROM aceites_termos at
LEFT JOIN inscricoes i ON at.inscricao_id = i.id
WHERE i.id IS NULL;

-- 4.3. Atualizar pagamentos ML órfãos (não deletar, apenas marcar)
UPDATE pagamentos_ml pm
LEFT JOIN inscricoes i ON pm.inscricao_id = i.id
SET pm.status = 'cancelado'
WHERE i.id IS NULL AND pm.status = 'pendente';

-- COMMIT;
-- ROLLBACK;
*/

