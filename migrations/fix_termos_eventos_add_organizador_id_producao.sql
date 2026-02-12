-- ============================================
-- CORREÇÃO: Adicionar organizador_id em termos_eventos
-- Para produção onde a migração não foi executada
-- Execute no phpMyAdmin ou MySQL da hospedagem (uma única vez)
-- ============================================

-- ETAPA 1: Adicionar coluna organizador_id (se não existir)
-- Se der erro "Duplicate column", a coluna já existe - pule para ETAPA 3
ALTER TABLE termos_eventos 
ADD COLUMN organizador_id INT(11) NULL AFTER id;

-- ETAPA 2: Popular organizador_id
-- Atribui ao primeiro organizador cadastrado
-- REQUISITO: Deve existir pelo menos um registro em organizadores
UPDATE termos_eventos te
CROSS JOIN (SELECT id FROM organizadores ORDER BY id LIMIT 1) o
SET te.organizador_id = o.id
WHERE te.organizador_id IS NULL;

-- Alternativa se sua tabela termos_eventos tiver a coluna evento_id:
-- UPDATE termos_eventos te
-- INNER JOIN eventos e ON te.evento_id = e.id
-- SET te.organizador_id = e.organizador_id
-- WHERE te.organizador_id IS NULL;

-- ETAPA 3: Tornar organizador_id NOT NULL
-- Se houver termos com organizador_id NULL (ex.: sem organizadores), este comando falhará
-- Nesse caso, crie um organizador antes ou deixe como NULL temporariamente
ALTER TABLE termos_eventos 
MODIFY COLUMN organizador_id INT(11) NOT NULL;

-- ETAPA 4: Adicionar índice
ALTER TABLE termos_eventos 
ADD KEY idx_organizador (organizador_id);

-- ETAPA 5 (opcional): Foreign key
-- Execute apenas se não houver termos órfãos
-- ALTER TABLE termos_eventos 
-- ADD CONSTRAINT fk_termos_organizador 
-- FOREIGN KEY (organizador_id) REFERENCES organizadores(id) ON DELETE CASCADE;
