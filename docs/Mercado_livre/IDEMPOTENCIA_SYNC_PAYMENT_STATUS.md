# ✅ Garantias de Idempotência - sync_payment_status.php

## 📋 Resumo

O arquivo `api/participante/sync_payment_status.php` foi completamente refatorado para ser **100% idempotente**. Isso significa que ele pode ser executado múltiplas vezes sem causar efeitos colaterais ou duplicações.

## 🔒 Garantias Implementadas

### 1. **Verificação Prévia Completa**

Antes de qualquer processamento, o script verifica se **TUDO já está completo**:

```php
$ja_completo = (
    $inscricao['status'] === 'confirmada' &&
    $inscricao['status_pagamento'] === 'pago' &&
    !empty($inscricao['numero_inscricao']) &&
    $inscricao['tem_registro_ml'] > 0 &&
    !empty($inscricao['pm_payment_id']) &&
    $inscricao['pm_status'] === 'pago'
);
```

**Se já completo**: Retorna imediatamente sem fazer NADA.

### 2. **Verificação de Registro em `pagamentos_ml`**

- ✅ Verifica se já existe registro por `inscricao_id`
- ✅ Verifica se já existe registro por `payment_id`
- ✅ Consulta a API se **não existir registro** ou se o status estiver **pendente/processando**
- ✅ Só cria registro se **NÃO** existir

### 3. **Verificação de Status da Inscrição**

- ✅ Só atualiza `status` se for diferente
- ✅ Só atualiza `status_pagamento` se for diferente
- ✅ Só gera `numero_inscricao` se estiver vazio
- ✅ Só atualiza `data_pagamento` se estiver NULL e status for 'pago'

### 4. **Verificação de Atualização em `pagamentos_ml`**

- ✅ Só atualiza registro existente se:
  - Status for diferente, OU
  - `payment_id` estiver vazio
- ✅ Não atualiza se já está correto

### 5. **Evita Consultas Desnecessárias à API**

- ✅ Só consulta API do Mercado Pago se:
  - Não existe registro em `pagamentos_ml`, **OU**
  - O status no `pagamentos_ml` ainda está **pendente/processando**
- ✅ Pode consultar por `payment_id` ou por `external_reference`
- ✅ Quando busca por `external_reference`, prioriza o pagamento mais recente
- ✅ Não consulta se já tem tudo completo

## 📊 Fluxo de Execução

```
1. Buscar inscrição com TODOS os dados necessários
   ↓
2. Verificar se JÁ ESTÁ COMPLETO
   ├─ SIM → Retornar sem fazer nada ✅
   └─ NÃO → Continuar
   ↓
3. Buscar registro em pagamentos_ml
   ↓
4. Se não existe registro:
   ├─ Consultar API do Mercado Pago
   ├─ Criar registro em pagamentos_ml (se necessário)
   └─ Atualizar inscrição (se necessário)
   ↓
5. Se já existe registro:
   ├─ Verificar se precisa atualizar
   ├─ Atualizar apenas se necessário
   └─ Atualizar inscrição (se necessário)
   ↓
6. Retornar resultado
```

## ✅ Cenários de Teste

### Cenário 1: Inscrição Já Completa
- **Estado**: Status='confirmada', status_pagamento='pago', tem registro em pagamentos_ml
- **Ação**: Nenhuma
- **Resultado**: Retorna imediatamente com `ja_completo: true`

### Cenário 2: Inscrição Pendente, Sem Registro em pagamentos_ml
- **Estado**: Status='pendente', sem registro em pagamentos_ml
- **Ação**: Consulta API, cria registro, atualiza inscrição
- **Resultado**: Tudo processado corretamente

### Cenário 3: Inscrição Confirmada, Sem Registro em pagamentos_ml
- **Estado**: Status='confirmada', mas sem registro em pagamentos_ml
- **Ação**: Consulta API, cria registro em pagamentos_ml
- **Resultado**: Registro criado, inscrição mantida como confirmada

### Cenário 4: Execução Múltipla (Idempotência)
- **Estado**: Já processado anteriormente
- **Ação**: Executar novamente
- **Resultado**: Nenhuma ação, retorna que já está completo

## 🔍 Logs de Idempotência

O script gera logs específicos quando detecta que não precisa fazer nada:

- `✅ Inscrição ID X já está completa - nenhuma ação necessária (idempotência)`
- `✅ Registro pagamentos_ml já existe - nenhuma ação necessária (idempotência)`
- `✅ Registro pagamentos_ml já está atualizado - nenhuma ação necessária (idempotência)`
- `✅ Inscrição ID X já está atualizada - nenhuma ação necessária (idempotência)`

## 🎯 Benefícios

1. **Segurança**: Pode ser executado múltiplas vezes sem problemas
2. **Performance**: Evita consultas desnecessárias à API
3. **Consistência**: Garante que dados não sejam duplicados
4. **Confiabilidade**: Não causa efeitos colaterais indesejados

## 📝 Notas Importantes

- O script **NÃO** reverte status já confirmado (`pago` só cai para `cancelado/rejeitado`)
- O script **NÃO** sobrescreve `numero_inscricao` se já existe
- O script **NÃO** cria registros duplicados em `pagamentos_ml`
- O script **NÃO** consulta API se já tem dados completos
