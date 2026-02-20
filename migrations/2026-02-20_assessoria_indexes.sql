-- ==========================================================
-- Migracao: Indexes complementares do modulo Assessoria
-- Data: 2026-02-20
-- Descricao: Adiciona indexes de performance nas colunas
--            status e filtros compostos frequentes
-- IMPORTANTE: Executar DEPOIS das migrations anteriores
-- ==========================================================

-- assessorias.status
CREATE INDEX IF NOT EXISTS idx_assessorias_status 
  ON assessorias(status);

-- assessoria_equipe.status
CREATE INDEX IF NOT EXISTS idx_assessoria_equipe_status 
  ON assessoria_equipe(status);

-- assessoria_atletas.status
CREATE INDEX IF NOT EXISTS idx_assessoria_atletas_status 
  ON assessoria_atletas(status);

-- assessoria_programas.status
CREATE INDEX IF NOT EXISTS idx_assessoria_programas_status 
  ON assessoria_programas(status);

-- assessoria_convites: compostos para queries frequentes
CREATE INDEX IF NOT EXISTS idx_convites_assessoria_status 
  ON assessoria_convites(assessoria_id, status);

CREATE INDEX IF NOT EXISTS idx_convites_atleta_status 
  ON assessoria_convites(atleta_usuario_id, status);

CREATE INDEX IF NOT EXISTS idx_convites_expira_em 
  ON assessoria_convites(expira_em);
