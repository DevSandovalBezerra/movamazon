-- Migração: Adicionar campo de classificação no questionário do evento
-- Data: 2025-12-23
-- Descrição: Permite classificar perguntas como 'evento' ou 'atleta' para melhor organização no frontend

-- 1. Adicionar coluna classificacao (evento ou atleta)
-- Usando procedimento para garantir idempotência (MySQL não suporta IF NOT EXISTS em ALTER TABLE ADD COLUMN)
SET @dbname = DATABASE();
SET @tablename = 'questionario_evento';
SET @columnname = 'classificacao';
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = @dbname 
     AND TABLE_NAME = @tablename 
     AND COLUMN_NAME = @columnname) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` ENUM(\'evento\', \'atleta\') DEFAULT \'evento\' AFTER `tipo_resposta`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 2. Atualizar perguntas existentes baseado em heurística
-- Perguntas sobre dados pessoais/emergência são do tipo 'atleta'
UPDATE questionario_evento 
SET classificacao = 'atleta' 
WHERE classificacao IS NULL OR classificacao = 'evento'
AND (
    texto LIKE '%contato%' 
    OR texto LIKE '%nome%peito%' 
    OR texto LIKE '%apelido%peito%'
    OR texto LIKE '%apto fisicamente%'
    OR texto LIKE '%incidentes%'
    OR texto LIKE '%emergência%'
    OR texto LIKE '%emergencia%'
    OR texto LIKE '%criança recebe%'
    OR texto LIKE '%Declara que este participante%'
);

-- 3. Garantir que perguntas sem classificação tenham valor padrão
UPDATE questionario_evento 
SET classificacao = 'evento' 
WHERE classificacao IS NULL;

