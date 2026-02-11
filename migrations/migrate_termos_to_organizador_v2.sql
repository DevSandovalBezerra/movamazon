-- Migration: Migrar termos_eventos de evento/modalidade para organizador
-- Data: 2025-12-20
-- Versão 2: Corrigida para compatibilidade MySQL
-- IMPORTANTE: Execute este script em ambiente de desenvolvimento/teste primeiro!

-- ============================================
-- ETAPA 1: BACKUP
-- ============================================
CREATE TABLE IF NOT EXISTS termos_eventos_backup AS SELECT * FROM termos_eventos;

-- ============================================
-- ETAPA 2: ADICIONAR COLUNA organizador_id
-- ============================================
ALTER TABLE termos_eventos 
ADD COLUMN organizador_id INT(11) NULL AFTER id;

-- ============================================
-- ETAPA 3: POPULAR organizador_id
-- ============================================
UPDATE termos_eventos te
INNER JOIN eventos e ON te.evento_id = e.id
SET te.organizador_id = e.organizador_id
WHERE te.organizador_id IS NULL;

-- ============================================
-- ETAPA 4: CONSOLIDAR TERMOS DUPLICADOS
-- ============================================
UPDATE termos_eventos t1
INNER JOIN (
    SELECT organizador_id, MAX(data_criacao) as max_data
    FROM termos_eventos
    WHERE organizador_id IS NOT NULL
    GROUP BY organizador_id
) t2 ON t1.organizador_id = t2.organizador_id
SET t1.ativo = 0
WHERE t1.data_criacao < t2.max_data;

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
-- IMPORTANTE: Execute estes comandos manualmente se os índices/colunas existirem
-- Ou use o script PHP helper abaixo

-- Verificar índices antes de remover (execute estas queries primeiro para verificar):
-- SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS 
-- WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'termos_eventos' 
-- AND INDEX_NAME IN ('evento_id', 'modalidade_id');

-- Se os índices existirem, execute:
-- ALTER TABLE termos_eventos DROP INDEX evento_id;
-- ALTER TABLE termos_eventos DROP INDEX modalidade_id;

-- Verificar colunas antes de remover (execute estas queries primeiro para verificar):
-- SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'termos_eventos' 
-- AND COLUMN_NAME IN ('evento_id', 'modalidade_id');

-- Se as colunas existirem, execute:
-- ALTER TABLE termos_eventos DROP COLUMN evento_id;
-- ALTER TABLE termos_eventos DROP COLUMN modalidade_id;

-- ============================================
-- ETAPA 6: TORNAR organizador_id NOT NULL
-- ============================================
-- DELETE FROM termos_eventos WHERE organizador_id IS NULL; -- Descomente se necessário

ALTER TABLE termos_eventos 
MODIFY COLUMN organizador_id INT(11) NOT NULL;

-- ============================================
-- ETAPA 7: ADICIONAR ÍNDICES E FOREIGN KEY
-- ============================================
ALTER TABLE termos_eventos 
ADD KEY idx_organizador (organizador_id);

ALTER TABLE termos_eventos 
ADD CONSTRAINT fk_termos_organizador 
FOREIGN KEY (organizador_id) 
REFERENCES organizadores(id) 
ON DELETE CASCADE;

-- ============================================
-- ETAPA 8: ADICIONAR CONSTRAINT DE UNICIDADE
-- ============================================
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

ALTER TABLE termos_eventos 
ADD UNIQUE KEY unique_organizador_ativo (organizador_id, ativo);
