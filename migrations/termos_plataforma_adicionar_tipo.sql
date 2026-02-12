-- ============================================
-- TERMOS LEGAIS: adicionar tipo e colunas de aceite
-- Permite múltiplos tipos: inscricao, anamnese, treino
-- ============================================

-- 1. Adicionar coluna tipo em termos_eventos
ALTER TABLE termos_eventos 
ADD COLUMN tipo VARCHAR(30) NOT NULL DEFAULT 'inscricao' 
COMMENT 'inscricao, anamnese ou treino' 
AFTER data_criacao;

-- 2. Atualizar registros existentes
UPDATE termos_eventos SET tipo = 'inscricao' WHERE tipo = '' OR tipo IS NULL;

-- 3. Índice para buscas por tipo e ativo
ALTER TABLE termos_eventos ADD INDEX idx_tipo_ativo (tipo, ativo);

-- 4. Colunas de aceite em anamneses (após status ou no final)
ALTER TABLE anamneses 
ADD COLUMN aceite_termos_anamnese TINYINT(1) DEFAULT 0 
COMMENT '1 = aceitou termos de anamnese',
ADD COLUMN data_aceite_termos_anamnese TIMESTAMP NULL DEFAULT NULL 
COMMENT 'Data/hora do aceite',
ADD COLUMN termos_id_anamnese INT(11) NULL DEFAULT NULL 
COMMENT 'ID do termo aceito';

-- 5. Colunas de aceite em planos_treino_gerados
ALTER TABLE planos_treino_gerados 
ADD COLUMN aceite_termos_treino TINYINT(1) DEFAULT 0 
COMMENT '1 = aceitou termos de treino' 
AFTER equipamento_geral,
ADD COLUMN data_aceite_termos_treino TIMESTAMP NULL DEFAULT NULL 
COMMENT 'Data/hora do aceite' 
AFTER aceite_termos_treino,
ADD COLUMN termos_id_treino INT(11) NULL DEFAULT NULL 
COMMENT 'ID do termo aceito' 
AFTER data_aceite_termos_treino;
