# ETAPA 5: Remover Colunas Antigas - Instruções

## Problema
MySQL não suporta `DROP INDEX IF EXISTS` ou `DROP COLUMN IF EXISTS`, então precisamos verificar antes de remover.

## Soluções Disponíveis

### Opção 1: Executar SQL Diretamente (RECOMENDADO)
Execute o arquivo `etapa5_remover_colunas_termos.sql` diretamente no seu cliente MySQL (phpMyAdmin, MySQL Workbench, etc.)

**Passos:**
1. Abra o arquivo `migrations/etapa5_remover_colunas_termos.sql`
2. Execute as queries de verificação primeiro
3. Execute apenas os comandos DROP para índices/colunas que existirem
4. Se algum comando der erro "Unknown key" ou "Unknown column", pode ignorar (significa que não existe)

### Opção 2: Usar Helper PHP (se PDO/MySQLi estiverem habilitados)

**Para habilitar PDO MySQL no PHP CLI (Windows/Laragon):**

1. Abra o arquivo `php.ini` do PHP CLI:
   - No Laragon: `C:\laragon\bin\php\php-X.X.X\php.ini`
   - Ou execute: `php --ini` para ver o caminho

2. Procure e descomente (remova o `;`):
   ```ini
   extension=pdo_mysql
   extension=mysqli
   ```

3. Reinicie o terminal e execute:
   ```bash
   php migrations/helper_drop_indexes_termos.php
   ```

### Opção 3: Executar Manualmente

Execute estas queries no MySQL:

```sql
-- 1. Verificar o que existe
SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'termos_eventos' 
AND INDEX_NAME IN ('evento_id', 'modalidade_id');

SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'termos_eventos' 
AND COLUMN_NAME IN ('evento_id', 'modalidade_id');

-- 2. Remover apenas o que existir (pode dar erro se não existir - ignore)
ALTER TABLE termos_eventos DROP INDEX evento_id;
ALTER TABLE termos_eventos DROP INDEX modalidade_id;
ALTER TABLE termos_eventos DROP COLUMN evento_id;
ALTER TABLE termos_eventos DROP COLUMN modalidade_id;
```

## Recomendação

**Use a Opção 1** - é a mais simples e não depende de configurações do PHP CLI.
