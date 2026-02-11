-- Migration: Garantir que os campos de endereço existem na tabela 'usuarios'
-- Data: 2025-12-24
-- Descrição: Campos necessários para pagamento com boleto bancário via Mercado Pago.
--            Esta migration é segura: verifica se os campos existem antes de criar.
--            Se os campos já existirem, a migration não causará erro.
--
-- Campos necessários:
--   - cep (zip_code)
--   - endereco (street_name)
--   - numero (street_number)
--   - bairro (neighborhood)
--   - cidade (city)
--   - uf (federal_unit)

SET @dbname = DATABASE();
SET @tablename = 'usuarios';

-- Verificar e adicionar coluna 'cep' se não existir
SET @columnname = 'cep';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE
            (TABLE_SCHEMA = @dbname)
            AND (TABLE_NAME = @tablename)
            AND (COLUMN_NAME = @columnname)
    ) > 0,
    "SELECT 'Campo cep já existe na tabela usuarios' AS resultado;",
    CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'CEP do endereço' AFTER pais;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar e adicionar coluna 'endereco' se não existir
SET @columnname = 'endereco';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE
            (TABLE_SCHEMA = @dbname)
            AND (TABLE_NAME = @tablename)
            AND (COLUMN_NAME = @columnname)
    ) > 0,
    "SELECT 'Campo endereco já existe na tabela usuarios' AS resultado;",
    CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Logradouro/rua do endereço' AFTER celular;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar e adicionar coluna 'numero' se não existir
SET @columnname = 'numero';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE
            (TABLE_SCHEMA = @dbname)
            AND (TABLE_NAME = @tablename)
            AND (COLUMN_NAME = @columnname)
    ) > 0,
    "SELECT 'Campo numero já existe na tabela usuarios' AS resultado;",
    CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Número do endereço' AFTER endereco;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar e adicionar coluna 'bairro' se não existir
SET @columnname = 'bairro';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE
            (TABLE_SCHEMA = @dbname)
            AND (TABLE_NAME = @tablename)
            AND (COLUMN_NAME = @columnname)
    ) > 0,
    "SELECT 'Campo bairro já existe na tabela usuarios' AS resultado;",
    CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Bairro do endereço' AFTER numero;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar e adicionar coluna 'cidade' se não existir
SET @columnname = 'cidade';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE
            (TABLE_SCHEMA = @dbname)
            AND (TABLE_NAME = @tablename)
            AND (COLUMN_NAME = @columnname)
    ) > 0,
    "SELECT 'Campo cidade já existe na tabela usuarios' AS resultado;",
    CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Cidade do endereço' AFTER bairro;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar e adicionar coluna 'uf' se não existir
SET @columnname = 'uf';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE
            (TABLE_SCHEMA = @dbname)
            AND (TABLE_NAME = @tablename)
            AND (COLUMN_NAME = @columnname)
    ) > 0,
    "SELECT 'Campo uf já existe na tabela usuarios' AS resultado;",
    CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " CHAR(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'UF (estado) do endereço' AFTER cidade;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

