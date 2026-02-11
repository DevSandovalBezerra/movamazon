# Scripts para Criar Tabela usuario_admin

Este diretório contém scripts para criar a tabela `usuario_admin` e populá-la com dados do arquivo `.env`.

## Opções de Execução

### Opção 1: Script PHP (Recomendado)

O script PHP lê automaticamente as variáveis do `.env` e gera o hash da senha corretamente.

**Executar:**
```bash
php migrations/create_usuario_admin_table_php.php
```

**Vantagens:**
- Lê automaticamente do `.env`
- Gera hash seguro usando `password_hash()` do PHP
- Mais fácil de usar

### Opção 2: Script SQL Manual

O script SQL requer que você substitua os valores manualmente antes de executar.

**Passos:**
1. Abra `migrations/create_usuario_admin_table.sql`
2. Substitua os valores nas variáveis:
   - `@admin_email` = valor de `ADMIN_EMAIL` do `.env` (linha 22)
   - `@admin_password` = valor de `ADMIN_PASSWORD` do `.env` (linha 23)
   - OU `@admin_password_hash` = valor de `ADMIN_PASSWORD_HASH` do `.env`
3. Execute o script no MySQL

**Para gerar hash da senha (se usar ADMIN_PASSWORD):**
```bash
php -r "echo password_hash('sua_senha_aqui', PASSWORD_DEFAULT);"
```

Depois cole o hash gerado em `@admin_password_hash` no script SQL.

## Estrutura da Tabela

A tabela `usuario_admin` contém:
- `id` - ID único
- `nome_completo` - Nome do administrador
- `email` - Email (único, usado para login)
- `senha` - Hash da senha
- `status` - ativo/inativo
- `data_cadastro` - Data de criação
- `ultimo_acesso` - Último acesso (atualizado automaticamente)
- `token_recuperacao` - Token para recuperação de senha
- `token_expira` - Data de expiração do token

## Verificação

Após executar qualquer um dos scripts, verifique se o registro foi criado:

```sql
SELECT * FROM usuario_admin WHERE email = 'seu_email@exemplo.com';
```

## Notas Importantes

1. **Segurança**: Use `ADMIN_PASSWORD_HASH` em produção em vez de `ADMIN_PASSWORD`
2. **Hash**: O hash deve ser gerado com `password_hash()` do PHP, não com `PASSWORD()` do MySQL
3. **Email único**: O email deve ser único na tabela
4. **Backup**: Faça backup antes de executar em produção

