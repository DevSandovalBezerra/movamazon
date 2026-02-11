-- Migration: Migrar termos_eventos de evento/modalidade para organizador
-- Data: 2025-12-20
-- Objetivo: Alterar estrutura para termos únicos por organizador (não mais por evento/modalidade)
-- IMPORTANTE: Executar este script em ambiente de desenvolvimento/teste primeiro!

-- ============================================
-- ETAPA 1: BACKUP
-- ============================================
-- Criar backup da tabela atual
CREATE TABLE IF NOT EXISTS termos_eventos_backup AS SELECT * FROM termos_eventos;

-- ============================================
-- ETAPA 2: ADICIONAR COLUNA organizador_id
-- ============================================
-- Adicionar coluna organizador_id (temporária, nullable)
ALTER TABLE termos_eventos 
ADD COLUMN organizador_id INT(11) NULL AFTER id;

-- ============================================
-- ETAPA 3: POPULAR organizador_id
-- ============================================
-- Popular organizador_id baseado no evento_id dos termos existentes
UPDATE termos_eventos te
INNER JOIN eventos e ON te.evento_id = e.id
SET te.organizador_id = e.organizador_id
WHERE te.organizador_id IS NULL;

-- ============================================
-- ETAPA 4: CONSOLIDAR TERMOS DUPLICADOS
-- ============================================
-- Para cada organizador com múltiplos termos, manter apenas o mais recente como ativo
-- Desativar todos os termos do organizador primeiro
UPDATE termos_eventos t1
INNER JOIN (
    SELECT organizador_id, MAX(data_criacao) as max_data
    FROM termos_eventos
    WHERE organizador_id IS NOT NULL
    GROUP BY organizador_id
) t2 ON t1.organizador_id = t2.organizador_id
SET t1.ativo = 0
WHERE t1.data_criacao < t2.max_data;

-- Ativar apenas o termo mais recente de cada organizador
UPDATE termos_eventos t1
INNER JOIN (
    SELECT organizador_id, MAX(data_criacao) as max_data
    FROM termos_eventos
    WHERE organizador_id IS NOT NULL
    GROUP BY organizador_id
) t2 ON t1.organizador_id = t2.organizador_id
SET t1.ativo = 1
WHERE t1.data_criacao = t2.max_data;

-- ============================================
-- ETAPA 5: REMOVER COLUNAS ANTIGAS
-- ============================================
-- ANTES DE CONTINUAR: Execute o arquivo migrations/ETAPA5_SIMPLES.sql
-- 
-- Ou execute estes comandos diretamente no MySQL:
-- (Se algum der erro "Unknown key/column", IGNORE - significa que não existe)
--
-- ALTER TABLE termos_eventos DROP INDEX evento_id;
-- ALTER TABLE termos_eventos DROP INDEX modalidade_id;
-- ALTER TABLE termos_eventos DROP COLUMN evento_id;
-- ALTER TABLE termos_eventos DROP COLUMN modalidade_id;

-- ============================================
-- ETAPA 6: TORNAR organizador_id NOT NULL
-- ============================================
-- Verificar se há termos sem organizador_id (não deveria acontecer, mas por segurança)
-- Se houver, deletar ou atribuir a um organizador padrão
-- DELETE FROM termos_eventos WHERE organizador_id IS NULL;

-- Tornar organizador_id NOT NULL
ALTER TABLE termos_eventos 
MODIFY COLUMN organizador_id INT(11) NOT NULL;

-- ============================================
-- ETAPA 7: ADICIONAR ÍNDICES E FOREIGN KEY
-- ============================================
-- Adicionar índice para organizador_id
ALTER TABLE termos_eventos 
ADD KEY idx_organizador (organizador_id);

-- Adicionar Foreign Key
ALTER TABLE termos_eventos 
ADD CONSTRAINT fk_termos_organizador 
FOREIGN KEY (organizador_id) 
REFERENCES organizadores(id) 
ON DELETE CASCADE;

-- ============================================
-- ETAPA 8: ADICIONAR CONSTRAINT DE UNICIDADE
-- ============================================
-- IMPORTANTE: Esta constraint garante apenas um termo ativo por organizador
-- Se falhar, significa que ainda há múltiplos termos ativos para o mesmo organizador
-- Nesse caso, execute novamente a ETAPA 4 antes de criar esta constraint

-- Primeiro, garantir que não há múltiplos termos ativos
-- (isso já foi feito na ETAPA 4, mas vamos garantir novamente)
UPDATE termos_eventos t1
INNER JOIN (
    SELECT organizador_id, MAX(data_criacao) as max_data
    FROM termos_eventos
    WHERE ativo = 1
    GROUP BY organizador_id
    HAVING COUNT(*) > 1
) t2 ON t1.organizador_id = t2.organizador_id
SET t1.ativo = 0
WHERE t1.data_criacao < t2.max_data;

-- Agora criar a constraint única
-- NOTA: Esta constraint pode falhar se houver múltiplos termos ativos
-- Se falhar, verifique e execute a query acima novamente
ALTER TABLE termos_eventos 
ADD UNIQUE KEY unique_organizador_ativo (organizador_id, ativo);

-- ============================================
-- VERIFICAÇÃO FINAL
-- ============================================
-- Verificar se a migração foi bem-sucedida
-- Execute estas queries para validar:

-- 1. Verificar se todos os termos têm organizador_id
-- SELECT COUNT(*) as total, COUNT(organizador_id) as com_organizador 
-- FROM termos_eventos;
-- Resultado esperado: total = com_organizador

-- 2. Verificar se há apenas um termo ativo por organizador
-- SELECT organizador_id, COUNT(*) as total_ativos 
-- FROM termos_eventos 
-- WHERE ativo = 1 
-- GROUP BY organizador_id 
-- HAVING total_ativos > 1;
-- Resultado esperado: 0 linhas

-- 3. Verificar estrutura final da tabela
-- DESCRIBE termos_eventos;
-- Deve mostrar: id, organizador_id, titulo, conteudo, versao, ativo, data_criacao

-- ============================================
-- ROLLBACK (se necessário)
-- ============================================
-- Se precisar reverter a migração:
-- DROP TABLE IF EXISTS termos_eventos;
-- CREATE TABLE termos_eventos AS SELECT * FROM termos_eventos_backup;
-- ALTER TABLE termos_eventos ADD PRIMARY KEY (id);
-- ALTER TABLE termos_eventos ADD KEY evento_id (evento_id);
-- ALTER TABLE termos_eventos ADD KEY modalidade_id (modalidade_id);

