# HOTFIX CRÍTICO - Erro 500 ao Salvar Configuração

**Data:** 30/01/2026  
**Erro:** SQLSTATE[23000] - Foreign Key Constraint Violation

---

## 🚨 **PROBLEMA CRÍTICO**

Erro de violação de chave estrangeira ao tentar salvar qualquer configuração:

```
SQLSTATE[23000]: Integrity constraint violation: 1452
Cannot add or update a child row: a foreign key constraint fails
(`brunor90_movamazon`.`config`, CONSTRAINT `fk_config_admin` 
FOREIGN KEY (`updated_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL)
```

### **Causa Raiz**

1. Tabela `config` tem FK `updated_by` → `usuarios.id`
2. Admins estão na tabela `usuario_admin`, **NÃO** em `usuarios`
3. Quando admin ID 2 tenta salvar, busca usuário ID 2 em `usuarios` → **FALHA**

---

## ✅ **SOLUÇÃO**

Remover a constraint de chave estrangeira problemática, permitindo que `updated_by` seja flexível.

### **Migration SQL**

```sql
-- Remover constraint problemática
ALTER TABLE `config` DROP FOREIGN KEY `fk_config_admin`;

-- Manter coluna mas sem constraint
ALTER TABLE `config` 
MODIFY COLUMN `updated_by` INT DEFAULT NULL 
COMMENT 'ID do admin/organizador que atualizou (referência flexível)';
```

---

## 📦 **ARQUIVOS PARA UPLOAD**

```
database/migrations/2026_01_30_fix_config_fk.sql
```

---

## 🧪 **EXECUÇÃO DA MIGRATION**

### **phpMyAdmin**
1. Acesse phpMyAdmin na hospedagem
2. Selecione banco `brunor90_movamazon`
3. Vá em **SQL**
4. Cole o conteúdo do arquivo `2026_01_30_fix_config_fk.sql`
5. Execute

### **CLI (se disponível)**
```bash
mysql -u brunor90_movamazon -p brunor90_movamazon < database/migrations/2026_01_30_fix_config_fk.sql
```

---

## ✅ **VERIFICAÇÃO PÓS-FIX**

1. Após executar a migration
2. Acesse: `/frontend/paginas/admin/index.php?page=configuracoes`
3. Busque: `treino.exigir_inscricao`
4. Edite e salve
5. ✅ Deve funcionar sem erro 500

---

## 📊 **IMPACTO**

- **Risco:** Baixo (remove constraint desnecessária)
- **Benefício:** Permite admins salvarem configurações
- **Efeito colateral:** Nenhum (auditoria continua funcionando)

---

## 🔄 **ORDEM DE DEPLOY**

1. ✅ Upload de `frontend/js/admin/configuracoes.js` (fix HTTP 400)
2. ✅ Executar migration `2026_01_30_fix_config_fk.sql` (fix HTTP 500)
3. ✅ Executar migration `2026_01_30_add_config_treino_inscricao.sql` (adicionar config)
4. ✅ Upload dos demais arquivos (feature treino sem inscrição)

---

**CRÍTICO: Executar migration ANTES de testar novamente!** 🔥
