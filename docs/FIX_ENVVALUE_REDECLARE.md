# CORREÇÃO - Erro Fatal envValue()

**Data:** 01/02/2026  
**Erro:** `Cannot redeclare envValue()`

---

## 🐛 **PROBLEMA**

```
PHP Fatal error: Cannot redeclare envValue() 
(previously declared in /api/db.php:44) 
in /api/participante/treino/generate.php on line 420
```

### **Causa:**
A função `envValue()` foi declarada localmente no arquivo `generate.php` (linhas 420-426), mas ela já existe no arquivo `db.php` que é incluído no início.

---

## ✅ **SOLUÇÃO APLICADA**

**Arquivo:** `api/participante/treino/generate.php`  
**Linhas:** 417-428

### **ANTES:**
```php
$openaiKey = ConfigHelper::get('ai.openai.api_key');
if (!$openaiKey) {
    // Fallback para .env se não estiver configurado no banco
    function envValue($key, $default = '') {  // ❌ REDECLARAÇÃO
        $val = getenv($key);
        if ($val === false) {
            $val = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
        }
        return (string) $val;
    }
    $openaiKey = envValue('OPENAI_API_KEY');
}
```

### **DEPOIS:**
```php
$openaiKey = ConfigHelper::get('ai.openai.api_key');
if (!$openaiKey) {
    // Fallback para .env se não estiver configurado no banco
    // Função envValue() já existe em db.php ✅
    $openaiKey = envValue('OPENAI_API_KEY');
}
```

---

## 📦 **ARQUIVO PARA UPLOAD**

```
api/participante/treino/generate.php
```

---

## ✅ **VERIFICAÇÃO**

Após upload, testar:
1. Acessar "Meus Treinos"
2. Clicar em "Gerar Treino"
3. ✅ Não deve mais dar erro 500

---

**CORREÇÃO APLICADA COM SUCESSO!** ✅
