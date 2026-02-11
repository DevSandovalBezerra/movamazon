# 🔍 Problema: Inscrição Confirmada Sem Webhook

## 📋 Resumo do Problema

Uma inscrição apareceu como **"Confirmada"** na interface, mas:
- ❌ Não há logs de webhook do Mercado Pago
- ❌ Não há registro na tabela `pagamentos_ml`
- ✅ O status foi atualizado diretamente na tabela `inscricoes`

## 🔎 Causa Raiz Identificada

O status foi alterado através do mecanismo de **sincronização automática** (`sync_payment_status.php`), não pelo webhook.

### Fluxo do Problema:

1. **Usuário acessa "Minhas Inscrições"** (`frontend/paginas/participante/index.php?page=minhas-inscricoes`)
2. **JavaScript detecta inscrições pendentes** (`frontend/js/participante/inscricoes.js`, linha 140-148)
3. **Chamada automática para `sync_payment_status.php`** para cada inscrição pendente
4. **`sync_payment_status.php` consulta diretamente a API do Mercado Pago** usando `external_reference` como `payment_id` (linha 65-89)
5. **Mercado Pago retorna status 'approved' (pago)**
6. **Sistema atualiza `inscricoes` diretamente** (linha 132-170)
7. **❌ NÃO cria/atualiza registro em `pagamentos_ml`**
8. **❌ NÃO passa pelo webhook**

### Evidências nos Logs:

```
[06-Feb-2026 10:29:12] [SYNC_PAYMENT_STATUS] PIX: consultou MP payment_id=145067143726, status=pago
[06-Feb-2026 10:29:12] [SYNC_PAYMENT_STATUS] Gerando numero_inscricao: MOV20260206-0018 para inscrição ID: 18
[06-Feb-2026 10:29:12] [SYNC_PAYMENT_STATUS] ✅ Inscrição ID 18 sincronizada: status='confirmada', status_pagamento='pago'
```

## ⚠️ Problemas Identificados

1. **Falta de registro em `pagamentos_ml`**: O `sync_payment_status.php` não cria/atualiza registro na tabela `pagamentos_ml` quando encontra um pagamento aprovado via consulta direta à API.

2. **Mascaramento de problemas do webhook**: O mecanismo de sincronização funciona como "fallback", mas pode mascarar problemas reais com o webhook do Mercado Pago.

3. **Inconsistência de dados**: A tabela `pagamentos_ml` deveria sempre ter um registro quando há um pagamento confirmado, independentemente de ter passado pelo webhook ou não.

## ✅ Solução Proposta

### 1. Corrigir `sync_payment_status.php` para criar/atualizar `pagamentos_ml`

Quando `sync_payment_status.php` encontrar um pagamento aprovado via consulta direta à API, ele deve:
- Criar ou atualizar registro em `pagamentos_ml` com os dados do pagamento
- Garantir que todos os campos necessários sejam preenchidos
- Manter consistência com o que o webhook faria

### 2. Adicionar logs detalhados

- Log quando webhook não é recebido mas pagamento é encontrado via sync
- Log quando registro em `pagamentos_ml` é criado via sync (não webhook)
- Alertar sobre possível problema com webhook

### 3. Investigar por que o webhook não foi recebido

- Verificar configuração do webhook no Mercado Pago
- Verificar se a URL do webhook está correta e acessível
- Verificar logs do servidor para erros de webhook
- Verificar se há bloqueios de firewall ou problemas de rede

## 📝 Arquivos Afetados

- `api/participante/sync_payment_status.php` - **PRECISA SER CORRIGIDO**
- `frontend/js/participante/inscricoes.js` - Chama sync automaticamente
- `api/mercadolivre/webhook.php` - Webhook que deveria ter processado o pagamento

## 🎯 Próximos Passos

1. ✅ Corrigir `sync_payment_status.php` para criar/atualizar `pagamentos_ml`
2. ✅ Adicionar logs detalhados para rastreamento
3. ⏳ Investigar configuração do webhook no Mercado Pago
4. ⏳ Verificar logs do servidor para erros de webhook
5. ⏳ Testar fluxo completo após correções
