# Correção Urgente - Toggle Treino Sem Inscrição

**Data:** 30/01/2026  
**Erro:** HTTP 400 "Parâmetros inválidos" ao salvar configuração

---

## 🐛 PROBLEMA IDENTIFICADO

O JavaScript estava enviando parâmetros incorretos para o backend:

```javascript
// ERRADO (linha 342)
body: JSON.stringify({ chave: state.current.chave, valor })

// CERTO
body: JSON.stringify({ key: state.current.chave, value: valor })
```

O backend PHP (`api/admin/config/update.php`) espera:
- `key` (não `chave`)
- `value` (não `valor`)

---

## ✅ CORREÇÃO APLICADA

**Arquivo:** `frontend/js/admin/configuracoes.js`  
**Linha:** 342

```javascript
const resp = await fetch(api('admin/config/update.php'), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ key: state.current.chave, value: valor })
});
```

---

## 📦 ARQUIVO PARA UPLOAD

```
frontend/js/admin/configuracoes.js
```

---

## 🧪 TESTE

1. Acesse: `/frontend/paginas/admin/index.php?page=configuracoes`
2. Busque: `treino.exigir_inscricao`
3. Clique em **Editar**
4. Altere o toggle
5. Clique em **Salvar Alterações**
6. ✅ Deve salvar sem erro 400

---

## 📊 STATUS

- [x] Erro identificado
- [x] Correção aplicada
- [x] Arquivo pronto para upload
- [ ] Upload realizado
- [ ] Teste em produção

**PRONTO PARA DEPLOY!** 🚀
