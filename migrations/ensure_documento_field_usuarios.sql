-- Migration: Garantir que os campos 'documento' e 'tipo_documento' existem na tabela 'usuarios'
-- Data: 2025-12-24
-- Descrição: Campos necessários para pagamento com boleto bancário (CPF obrigatório).
--            Esta migration é segura: verifica se os campos existem antes de criar.
--            Se os campos já existirem, a migration não causará erro.

-- Verificar e adicionar coluna 'documento' se não existir
SET @dbname = DATABASE();
SET @tablename = 'usuarios';
SET @columnname = 'documento';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE
            (TABLE_SCHEMA = @dbname)
            AND (TABLE_NAME = @tablename)
            AND (COLUMN_NAME = @columnname)
    ) > 0,
    "SELECT 'Campo documento já existe na tabela usuarios' AS resultado;",
    CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'CPF ou documento do usuário' AFTER data_nascimento;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar e adicionar coluna 'tipo_documento' se não existir
SET @columnname2 = 'tipo_documento';
SET @preparedStatement2 = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE
            (TABLE_SCHEMA = @dbname)
            AND (TABLE_NAME = @tablename)
            AND (COLUMN_NAME = @columnname2)
    ) > 0,
    "SELECT 'Campo tipo_documento já existe na tabela usuarios' AS resultado;",
    CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname2, " VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tipo de documento (CPF, CNH, etc)' AFTER data_nascimento;")
));
PREPARE alterIfNotExists2 FROM @preparedStatement2;
EXECUTE alterIfNotExists2;
DEALLOCATE PREPARE alterIfNotExists2;

