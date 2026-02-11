-- ============================================
-- MIGRAÇÃO: Consolidar payments_ml para pagamentos_ml
-- Data: 2026-01-21
-- Descrição: Migra dados da tabela payments_ml (se existir) para pagamentos_ml
--            e remove a tabela payments_ml após migração
-- ============================================

-- IMPORTANTE: Execute este script em ambiente de desenvolvimento primeiro!
-- Faça backup do banco de dados antes de executar em produção!

SET @dbname = DATABASE();

-- ============================================
-- FASE 1: VERIFICAÇÃO
-- ============================================

-- Verificar se tabela payments_ml existe
SET @payments_ml_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = @dbname 
      AND TABLE_NAME = 'payments_ml'
);

-- Verificar se tabela pagamentos_ml existe
SET @pagamentos_ml_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = @dbname 
      AND TABLE_NAME = 'pagamentos_ml'
);

SELECT 
    CASE 
        WHEN @payments_ml_exists > 0 THEN 'payments_ml existe'
        ELSE 'payments_ml NÃO existe'
    END as status_payments_ml,
    CASE 
        WHEN @pagamentos_ml_exists > 0 THEN 'pagamentos_ml existe'
        ELSE 'pagamentos_ml NÃO existe'
    END as status_pagamentos_ml;

-- ============================================
-- FASE 2: MIGRAÇÃO DE DADOS (se payments_ml existir)
-- ============================================

-- Só executar se payments_ml existir E pagamentos_ml existir
SET @sql_migracao = IF(
    @payments_ml_exists > 0 AND @pagamentos_ml_exists > 0,
    '
    -- Contar registros em payments_ml
    SET @total_payments_ml = (SELECT COUNT(*) FROM payments_ml);
    
    -- Contar registros que já existem em pagamentos_ml (por payment_id)
    SET @duplicados = (
        SELECT COUNT(*) 
        FROM payments_ml pm1
        INNER JOIN pagamentos_ml pm2 ON pm1.payment_id = pm2.payment_id 
            AND pm1.payment_id IS NOT NULL 
            AND pm1.payment_id != ""
    );
    
    -- Contar registros que serão migrados (não duplicados)
    SET @novos_registros = @total_payments_ml - @duplicados;
    
    -- Inserir registros de payments_ml que não existem em pagamentos_ml
    -- (baseado em payment_id quando disponível, ou inscricao_id + preference_id)
    INSERT INTO pagamentos_ml (
        inscricao_id, 
        preference_id, 
        payment_id, 
        init_point, 
        status, 
        data_criacao, 
        data_atualizacao, 
        dados_pagamento, 
        valor_pago, 
        metodo_pagamento, 
        parcelas, 
        taxa_ml, 
        user_id, 
        created
    )
    SELECT 
        pm1.inscricao_id,
        pm1.preference_id,
        pm1.payment_id,
        pm1.init_point,
        pm1.status,
        pm1.data_criacao,
        pm1.data_atualizacao,
        pm1.dados_pagamento,
        pm1.valor_pago,
        pm1.metodo_pagamento,
        pm1.parcelas,
        pm1.taxa_ml,
        pm1.user_id,
        pm1.created
    FROM payments_ml pm1
    LEFT JOIN pagamentos_ml pm2 ON (
        -- Tentar match por payment_id primeiro (mais confiável)
        (pm1.payment_id IS NOT NULL AND pm1.payment_id != "" AND pm1.payment_id = pm2.payment_id)
        OR
        -- Fallback: match por inscricao_id + preference_id
        (pm1.inscricao_id = pm2.inscricao_id AND pm1.preference_id = pm2.preference_id)
    )
    WHERE pm2.id IS NULL;
    
    SELECT 
        "Migração concluída" as resultado,
        @total_payments_ml as total_em_payments_ml,
        @duplicados as registros_duplicados,
        @novos_registros as registros_migrados,
        (SELECT COUNT(*) FROM pagamentos_ml) as total_em_pagamentos_ml;
    ',
    CONCAT('
    SELECT 
        "',
        IF(@payments_ml_exists = 0, 'payments_ml não existe - migração não necessária', 
           IF(@pagamentos_ml_exists = 0, 'pagamentos_ml não existe - crie a tabela primeiro', 
              'Erro desconhecido')),
        '" as mensagem;
    ')
);

PREPARE stmt_migracao FROM @sql_migracao;
EXECUTE stmt_migracao;
DEALLOCATE PREPARE stmt_migracao;

-- ============================================
-- FASE 3: VALIDAÇÃO
-- ============================================

-- Verificar se há registros órfãos em payments_ml que não foram migrados
SET @sql_validacao = IF(
    @payments_ml_exists > 0 AND @pagamentos_ml_exists > 0,
    '
    SELECT 
        "Registros em payments_ml não migrados" as tipo,
        COUNT(*) as total
    FROM payments_ml pm1
    LEFT JOIN pagamentos_ml pm2 ON (
        (pm1.payment_id IS NOT NULL AND pm1.payment_id != "" AND pm1.payment_id = pm2.payment_id)
        OR
        (pm1.inscricao_id = pm2.inscricao_id AND pm1.preference_id = pm2.preference_id)
    )
    WHERE pm2.id IS NULL;
    ',
    'SELECT "Validação não necessária" as mensagem;'
);

PREPARE stmt_validacao FROM @sql_validacao;
EXECUTE stmt_validacao;
DEALLOCATE PREPARE stmt_validacao;

-- ============================================
-- FASE 4: REMOÇÃO DA TABELA payments_ml (OPCIONAL)
-- ============================================

-- ATENÇÃO: Descomente as linhas abaixo APENAS após validar que a migração foi bem-sucedida!
-- Execute manualmente após revisar os resultados acima.

/*
-- Remover tabela payments_ml após migração bem-sucedida
DROP TABLE IF EXISTS payments_ml;

SELECT "Tabela payments_ml removida com sucesso" as resultado;
*/

-- ============================================
-- RESUMO
-- ============================================

SELECT 
    "=== RESUMO DA MIGRAÇÃO ===" as titulo,
    CASE 
        WHEN @payments_ml_exists = 0 THEN "✅ Nenhuma ação necessária - payments_ml não existe"
        WHEN @pagamentos_ml_exists = 0 THEN "⚠️ ERRO - pagamentos_ml não existe. Crie a tabela primeiro."
        ELSE "✅ Migração executada. Revise os resultados acima antes de remover payments_ml."
    END as status_final;
