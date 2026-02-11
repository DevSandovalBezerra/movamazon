# 🔔 Configuração de Webhooks do Mercado Pago

**Data:** 30/01/2026  
**Objetivo:** Guia completo para configurar e diagnosticar webhooks de pagamento

---

## 📋 Índice

1. [Problema Identificado](#problema-identificado)
2. [Solução Implementada](#solução-implementada)
3. [Configuração no DevCenter](#configuração-no-devcenter)
4. [Testes e Diagnóstico](#testes-e-diagnóstico)
5. [Troubleshooting](#troubleshooting)

---

## 🔴 Problema Identificado

### Sintomas
- ✅ Pagamentos sendo processados no Mercado Pago
- ❌ Pagamentos **NÃO** sendo salvos na tabela `pagamentos`
- ❌ Sistema não atualizando status das inscrições
- ❌ Emails de confirmação não sendo enviados

### Causa Raiz (Diagnosticada via MCP)

Segundo a [documentação oficial do Mercado Pago](https://developers.mercadolivre.com.br/pt_br/produto-receba-notificacoes):

> **"Atualize sua integração para ter sempre retorno, HTTP 200 e em 500 milissegundos após o recebimento da notificação, com isso você evitará que os tópicos de suas notificações sejam desativados por fall back."**

**Possíveis causas:**

1. **Webhook demora > 500ms para responder** → Mercado Pago desativa o tópico
2. **Webhook não responde HTTP 200** → Notificações são perdidas
3. **Tópico "payments" não está ativo** no DevCenter
4. **URL do webhook incorreta** ou inacessível

---

## ✅ Solução Implementada

### 1. Otimização do Webhook (`api/mercadolivre/webhook.php`)

**ANTES (Problema):**
- Processamento **síncrono** completo
- Consulta API Mercado Pago **antes** de responder
- Atualização de 3 tabelas **antes** de responder
- Envio de email **antes** de responder
- ⏱️ Tempo total: **2-5 segundos**

**DEPOIS (Otimizado):**
```php
// 1. Recebe notificação
// 2. Valida dados básicos (50ms)
// 3. Adiciona à fila (20ms)
// 4. RESPONDE HTTP 200 IMEDIATAMENTE (< 100ms) ✅
// 5. Fecha conexão HTTP
// 6. Processa dados em background (assíncrono)
```

**Resultado:** ⚡ **Resposta em < 100ms** (5x mais rápido que o limite de 500ms)

### 2. Sistema de Fila

- Notificações são salvas em `logs/webhook_queue.json`
- Processamento acontece **após** resposta HTTP 200
- Usa `fastcgi_finish_request()` para fechar conexão
- Logs detalhados em `logs/webhook_mp.log`

### 3. Scripts de Diagnóstico

| Script | Função |
|--------|--------|
| `api/diagnostico/testar_webhook.php` | Simula notificação do Mercado Pago |
| `api/diagnostico/verificar_missed_feeds.php` | Lista notificações perdidas |
| `api/diagnostico/listar_payment_methods.php` | Lista métodos de pagamento ativos |

---

## 🔧 Configuração no DevCenter

### Passo 1: Acessar o DevCenter

1. Acesse: **https://developers.mercadolivre.com.br/devcenter/**
2. Faça login com sua conta do Mercado Livre
3. Clique em **"Minhas aplicações"**
4. Selecione sua aplicação (MovAmazon)

### Passo 2: Configurar Notificações

1. Clique em **"Editar"** na sua aplicação
2. Role até a seção **"Tópicos"**
3. **✅ MARQUE** o tópico **"payments"**
4. No campo **"URL de retorno de notificações"**, insira:
   ```
   https://www.movamazon.com.br/api/mercadolivre/webhook.php
   ```

5. Clique em **"Salvar"**

### Passo 3: Verificar Configuração

Após salvar, você verá:

- ✅ **Tópico "payments"** marcado
- ✅ **URL do webhook** configurada
- ✅ Status: **Ativo**

**⚠️ IMPORTANTE:**

- A URL deve usar **HTTPS** (obrigatório)
- A URL deve ser **acessível publicamente**
- O servidor deve responder **HTTP 200** em **< 500ms**

---

## 🧪 Testes e Diagnóstico

### Teste 1: Simular Notificação

```bash
# No servidor
php api/diagnostico/testar_webhook.php
```

**OU** acesse via navegador:
```
https://www.movamazon.com.br/api/diagnostico/testar_webhook.php
```

**O que verificar:**
- ✅ Resposta HTTP 200
- ✅ Tempo < 500ms (idealmente < 100ms)
- ✅ Log criado em `logs/webhook_mp.log`
- ✅ Entrada na fila `logs/webhook_queue.json`

### Teste 2: Verificar Notificações Perdidas

```bash
# No servidor
php api/diagnostico/verificar_missed_feeds.php
```

**Pré-requisito:** Adicione no `.env`:
```env
ML_APP_ID=SEU_APP_ID_AQUI
```

**Como obter APP_ID:**
1. DevCenter → Sua aplicação
2. Copie o **"client_id"** (esse é o APP_ID)

**Resultado esperado:**
```
✅ EXCELENTE! Nenhuma notificação perdida!
```

**Se houver notificações perdidas:**
```
⚠️ ATENÇÃO: 5 notificação(ões) perdida(s) encontrada(s)!
```

### Teste 3: Pagamento Real

1. Faça uma inscrição no sistema
2. Gere um PIX ou Boleto
3. Efetue o pagamento (pode ser valor mínimo R$ 5,00)
4. Aguarde 1-2 minutos
5. Verifique:

```sql
-- No banco de dados
SELECT * FROM pagamentos WHERE inscricao_id = XXX;
SELECT * FROM inscricoes WHERE id = XXX;
```

**Ou verifique os logs:**
```bash
tail -f logs/webhook_mp.log
tail -f logs/inscricoes_pagamentos.log
```

---

## 🔍 Troubleshooting

### Problema 1: Webhook não está sendo chamado

**Sintomas:**
- Pagamento aprovado no Mercado Pago
- Nenhum log em `webhook_mp.log`
- Nenhuma entrada em `php_errors.log`

**Soluções:**

1. **Verificar se o tópico está ativo:**
   - DevCenter → Aplicação → Tópicos
   - Confirme que "payments" está ✅ marcado

2. **Verificar URL do webhook:**
   - Deve ser: `https://www.movamazon.com.br/api/mercadolivre/webhook.php`
   - Teste acessando direto no navegador (deve retornar algo)

3. **Verificar firewall/hospedagem:**
   - A URL deve ser acessível de fora
   - IPs do Mercado Pago que fazem requisições:
     - 54.88.218.97
     - 18.215.140.160
     - 18.213.114.129
     - 18.206.34.84

4. **Reinscrever-se nos tópicos:**
   - Se o webhook foi desativado por fallback, você precisa:
   - DevCenter → Aplicação → Desmarcar "payments"
   - Salvar
   - Marcar novamente "payments"
   - Salvar

### Problema 2: Webhook está sendo chamado mas não salva no banco

**Sintomas:**
- Log em `webhook_mp.log` mostra notificação recebida
- Dados não aparecem na tabela `pagamentos`
- Erro em `php_errors.log`

**Soluções:**

1. **Verificar logs de erro:**
```bash
tail -100 logs/php_errors.log | grep WEBHOOK
```

2. **Verificar se external_reference existe:**
```sql
SELECT * FROM inscricoes WHERE external_reference = 'VALOR_DO_LOG';
```

3. **Verificar credenciais:**
   - Access token deve ser de **PRODUÇÃO**
   - Sem nenhum código de sandbox no sistema

### Problema 3: Resposta > 500ms

**Sintomas:**
- `verificar_missed_feeds.php` mostra notificações com `req_time > 500`
- Webhook desativado automaticamente

**Solução:**

1. **Aplicar webhook otimizado:**
   - Faça upload do `webhook.php` atualizado
   - Versão otimizada responde em < 100ms

2. **Otimizar servidor:**
   - PHP 8.0+ (melhor performance)
   - OPcache ativado
   - Conexão de banco rápida

3. **Confirmar otimização:**
```bash
php api/diagnostico/testar_webhook.php
# Deve mostrar: ⚡ Resposta em XXms ✅ EXCELENTE (< 500ms)
```

### Problema 4: External Reference não encontrado

**Sintomas:**
```
⚠️ Inscrição não encontrada: ref=INSCRIÇÃO_123
```

**Soluções:**

1. **Verificar se a inscrição foi salva:**
```sql
SELECT * FROM inscricoes WHERE id = 123;
-- Verificar se external_reference está preenchido
```

2. **Verificar fluxo de criação:**
   - `salvar_ficha.php` deve salvar `external_reference`
   - Formato: `INSCRIÇÃO_[ID]`

3. **Checar logs da criação:**
```bash
grep "INSCRIÇÃO_123" logs/inscricoes_pagamentos.log
```

### Problema 5: Erro ao sincronizar status / "Erro ao consultar pagamento"

**Sintomas:**
- Modal: "Erro ao sincronizar status: Erro ao consultar pagamento: Si quieres conocer los recursos..."
- Webhook não atualiza e a sincronização manual também falha

**Causas comuns:**

1. **Access token inválido ou expirado**
   - No `.env`, confirme `APP_Acess_token` (ou `ML_ACCESS_TOKEN_PROD`) com o token de **produção** do DevCenter.
   - Gere um novo token em DevCenter → Sua aplicação → Credenciais de produção, se necessário.

2. **Webhook não está sendo chamado**
   - Sem webhook, a tabela `pagamentos_ml` não recebe o `payment_id` (só a `external_reference` fica na inscrição).
   - A rotina de sync usa `payment_id` ou, na falta dele, `external_reference`. Desde a correção no `PaymentHelper`, quando só há `external_reference` (ex: MOVAMAZON_27), o sistema **busca o pagamento na API do MP por external_reference** e depois consulta o status. Se a busca não retornar nada, verifique se a URL do webhook está correta e acessível (veja Problema 1).

3. **URL do webhook no .env e no DevCenter**
   - `.env`: `ML_NOTIFICATION_URL=https://www.movamazon.com.br/api/mercadolivre/webhook.php`
   - DevCenter → Aplicação → URL de retorno de notificações: **exatamente** a mesma URL, com HTTPS.
   - Teste: acesse a URL no navegador; deve responder (não 404).

4. **Assinatura do webhook (x-signature)**
   - Se no `.env` estiver configurado `ML_WEBHOOK_SECRET`, o webhook valida o header `x-signature`. O valor deve ser o mesmo configurado no painel do Mercado Pago para a aplicação. Se estiver errado, o webhook rejeita e retorna 200 sem processar (veja logs).

**O que foi corrigido no código:**
- `api/mercadolivre/payment_helper.php`: quando o valor passado não é um ID numérico (ex: MOVAMAZON_27), o helper chama a API de **search** do Mercado Pago por `external_reference`, obtém o `payment_id` real e depois consulta o pagamento. Assim a sincronização manual passa a funcionar mesmo quando o webhook ainda não registrou o `payment_id`.

---

## 📊 Monitoramento Contínuo

### Logs Importantes

| Arquivo | Conteúdo |
|---------|----------|
| `logs/webhook_mp.log` | Todas as notificações recebidas |
| `logs/inscricoes_pagamentos.log` | Processamento detalhado |
| `logs/php_errors.log` | Erros críticos |
| `logs/webhook_queue.json` | Fila de processamento |

### Comandos Úteis

```bash
# Ver últimas notificações
tail -20 logs/webhook_mp.log

# Monitorar em tempo real
tail -f logs/webhook_mp.log

# Buscar payment específico
grep "payment_id_123" logs/webhook_mp.log

# Ver erros do webhook
grep WEBHOOK logs/php_errors.log

# Ver fila atual
cat logs/webhook_queue.json | python -m json.tool
```

### Métricas Esperadas

✅ **Sistema saudável:**
- Tempo de resposta: < 100ms
- HTTP Status: 200
- Notificações perdidas: 0
- Taxa de sucesso: 100%

⚠️ **Atenção necessária:**
- Tempo de resposta: 100-500ms
- Notificações perdidas: 1-5
- Erros ocasionais em `php_errors.log`

❌ **Problema crítico:**
- Tempo de resposta: > 500ms
- HTTP Status: 500, 400
- Notificações perdidas: > 5
- Webhook desativado

---

## 📝 Checklist Pós-Implementação

Após fazer upload dos arquivos:

- [ ] Webhook otimizado (`webhook.php`) enviado para hospedagem
- [ ] Scripts de diagnóstico enviados (`api/diagnostico/*.php`)
- [ ] DevCenter configurado (tópico "payments" ativo)
- [ ] URL do webhook configurada corretamente
- [ ] `.env` com `ML_APP_ID` configurado
- [ ] Teste manual executado (`testar_webhook.php`)
- [ ] Verified missed_feeds (`verificar_missed_feeds.php`)
- [ ] Pagamento real testado e confirmado
- [ ] Logs monitorados por 24h
- [ ] Documentação lida e compreendida

---

## 🆘 Suporte

Se após seguir todos os passos o problema persistir:

1. **Colete informações:**
   ```bash
   # Executar diagnósticos
   php api/diagnostico/testar_webhook.php > diagnostico.txt
   php api/diagnostico/verificar_missed_feeds.php >> diagnostico.txt
   
   # Logs recentes
   tail -100 logs/webhook_mp.log >> diagnostico.txt
   tail -100 logs/php_errors.log >> diagnostico.txt
   ```

2. **Verificar configuração:**
   - Screenshot do DevCenter mostrando tópicos ativos
   - URL do webhook configurada
   - Resultado do teste manual

3. **Contatar suporte do Mercado Pago:**
   - Discord: Mercado Pago Developers
   - Email: developers@mercadopago.com
   - Documentação: https://www.mercadopago.com.br/developers

---

## 📚 Referências

- [Documentação oficial de Notificações](https://developers.mercadolivre.com.br/pt_br/produto-receba-notificacoes)
- [Criar aplicação no Mercado Livre](https://developers.mercadolivre.com.br/pt_br/crie-uma-aplicacao-no-mercado-livre)
- [Gerenciamento de Pagamentos](https://developers.mercadolivre.com.br/pt_br/gerenciamento-de-pagamentos)
- [Status do Sistema](https://status.mercadopago.com/)

---

**Última atualização:** 30/01/2026  
**Versão do sistema:** 2.0 (Webhook Otimizado)
