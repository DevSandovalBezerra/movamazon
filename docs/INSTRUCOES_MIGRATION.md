# MIGRATION - Instruções de Execução

## ⚠️ IMPORTANTE

O phpMyAdmin pode ter problemas com prepared statements. Use a **versão SIMPLES**.

---

## 🚀 OPÇÃO 1: Versão Simples (RECOMENDADO)

**Arquivo:** `2026_01_30_fix_config_fk_SIMPLES.sql`

### **Executar no phpMyAdmin:**

Cole e execute **CADA COMANDO SEPARADAMENTE**:

```sql
-- Comando 1
ALTER TABLE `config` DROP FOREIGN KEY `fk_config_admin`;
```

**Se der erro "constraint não existe"**: OK, ignore e continue!

```sql
-- Comando 2
ALTER TABLE `config_historico` DROP FOREIGN KEY `fk_config_historico_admin`;
```

**Se der erro "constraint não existe"**: OK, ignore e continue!

```sql
-- Comando 3
ALTER TABLE `config` 
MODIFY COLUMN `updated_by` INT DEFAULT NULL;
```

```sql
-- Comando 4
ALTER TABLE `config_historico` 
MODIFY COLUMN `alterado_por` INT DEFAULT NULL;
```

---

## 🔍 VERIFICAR SE FUNCIONOU

Execute para verificar:

```sql
-- Ver estrutura da tabela config
SHOW CREATE TABLE `config`;

-- Ver estrutura da tabela config_historico
SHOW CREATE TABLE `config_historico`;
```

Se não aparecer `FOREIGN KEY` nas colunas `updated_by` e `alterado_por`, está correto! ✅

---

## 📋 CHECKLIST

- [ ] Executar comando 1 (ignorar erro se houver)
- [ ] Executar comando 2 (ignorar erro se houver)
- [ ] Executar comando 3 (deve funcionar)
- [ ] Executar comando 4 (deve funcionar)
- [ ] Verificar estrutura das tabelas
- [ ] Testar salvamento de configuração

---

## ❓ SE DER ERRO

Se algum comando der erro diferente de "constraint não existe":

1. Anote a mensagem de erro completa
2. Me envie o erro
3. Vou criar solução específica

---

**COMEÇAR PELA OPÇÃO 1!** 🚀
