# Lista de Arquivos para Upload - Toggle Treino Sem Inscrição

**Data:** 01/02/2026  
**Feature:** Toggle administrativo + Correções de bugs

---

## 📦 ARQUIVOS PARA UPLOAD (EM ORDEM)

### **1. Backend API**

```
api/participante/treino/generate.php
api/participante/treino/get.php
```

**Mudanças:**
- `generate.php`: Lógica condicional + fix envValue()
- `get.php`: Aceita inscrição mock em modo desenvolvimento

---

### **2. Frontend Participante**

```
frontend/paginas/participante/meus-treinos.php
```

**Mudanças:**
- Modo mock quando toggle desativado
- Aviso visual amarelo em modo desenvolvimento

---

### **3. Frontend Admin**

```
frontend/paginas/admin/configuracoes.php
frontend/js/admin/configuracoes.js
```

**Mudanças:**
- Badge "Validação Treino" no dashboard
- JavaScript atualiza status automaticamente
- Fix parâmetros (key/value)

---

### **4. Migrations SQL (EXECUTAR NO phpMyAdmin)**

#### **A) Fix FK (JÁ DEVE TER SIDO EXECUTADO)**
```sql
ALTER TABLE `config_historico` 
DROP FOREIGN KEY `fk_config_historico_admin`;
```

#### **B) Adicionar Config (SE NÃO EXECUTOU)**
```sql
INSERT INTO `config` (`chave`, `valor`, `tipo`, `categoria`, `descricao`, `editavel`, `visivel`)
VALUES ('treino.exigir_inscricao', 'true', 'boolean', 'treino', 
        'Exigir inscrição confirmada para gerar treino', 1, 1);
```

---

## ✅ CHECKLIST DE DEPLOY

- [ ] **1. Verificar migrations SQL executadas**
  - [ ] FK removida de `config_historico`
  - [ ] Config `treino.exigir_inscricao` existe no banco

- [ ] **2. Upload arquivos backend**
  - [ ] `api/participante/treino/generate.php`
  - [ ] `api/participante/treino/get.php`

- [ ] **3. Upload arquivos frontend participante**
  - [ ] `frontend/paginas/participante/meus-treinos.php`

- [ ] **4. Upload arquivos frontend admin**
  - [ ] `frontend/paginas/admin/configuracoes.php`
  - [ ] `frontend/js/admin/configuracoes.js`

- [ ] **5. Testes**
  - [ ] Salvar configuração no admin (deve funcionar)
  - [ ] Desativar toggle `treino.exigir_inscricao`
  - [ ] Ver "Meus Treinos" (deve mostrar inscrição mock)
  - [ ] Gerar treino mock (deve funcionar)
  - [ ] Reativar toggle
  - [ ] Verificar que volta ao modo produção

---

## 🔍 VERIFICAÇÃO DE CADA CORREÇÃO

### **Correção 1: envValue() redeclarado**
- **Arquivo:** `api/participante/treino/generate.php`
- **Teste:** Gerar treino não deve dar erro 500

### **Correção 2: get.php validação de inscrição**
- **Arquivo:** `api/participante/treino/get.php`
- **Teste:** Buscar treino mock deve funcionar em modo desenvolvimento

### **Correção 3: FK config_historico**
- **SQL:** `ALTER TABLE config_historico DROP FOREIGN KEY`
- **Teste:** Salvar configuração não deve dar erro 500

### **Correção 4: Parâmetros JS**
- **Arquivo:** `frontend/js/admin/configuracoes.js`
- **Teste:** Salvar configuração deve usar key/value (não chave/valor)

---

## 📊 RESUMO DE MUDANÇAS

| Arquivo | Tipo | Descrição |
|---------|------|-----------|
| `generate.php` | Fix + Feature | Remove envValue duplicado + modo mock |
| `get.php` | Feature | Aceita inscrição mock |
| `meus-treinos.php` | Feature | Exibe inscrição mock |
| `configuracoes.php` | Feature | Badge status treino |
| `configuracoes.js` | Fix + Feature | Corrige params + badge status |
| SQL | Fix | Remove FK problemática |

---

**TOTAL: 5 arquivos PHP/JS + 1 SQL** 🚀
