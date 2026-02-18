# ðŸ”” ConfiguraÃ§Ã£o de Webhooks do Mercado Pago

**Data:** 30/01/2026  
**Objetivo:** Guia completo para configurar e diagnosticar webhooks de pagamento

---

## ðŸ“‹ Ãndice

1. [Problema Identificado](#problema-identificado)
2. [SoluÃ§Ã£o Implementada](#soluÃ§Ã£o-implementada)
3. [ConfiguraÃ§Ã£o no DevCenter](#configuraÃ§Ã£o-no-devcenter)
4. [Testes e DiagnÃ³stico](#testes-e-diagnÃ³stico)
5. [Troubleshooting](#troubleshooting)

---

## ðŸ”´ Problema Identificado

### Sintomas
- âœ… Pagamentos sendo processados no Mercado Pago
- âŒ Pagamentos **NÃƒO** sendo salvos na tabela `pagamentos`
- âŒ Sistema nÃ£o atualizando status das inscriÃ§Ãµes
- âŒ Emails de confirmaÃ§Ã£o nÃ£o sendo enviados

### Causa Raiz (Diagnosticada via MCP)

Segundo a [documentaÃ§Ã£o oficial do Mercado Pago](https://developers.mercadolivre.com.br/pt_br/produto-receba-notificacoes):

> **"Atualize sua integraÃ§Ã£o para ter sempre retorno, HTTP 200 e em 500 milissegundos apÃ³s o recebimento da notificaÃ§Ã£o, com isso vocÃª evitarÃ¡ que os tÃ³picos de suas notificaÃ§Ãµes sejam desativados por fall back."**

**PossÃ­veis causas:**

1. **Webhook demora > 500ms para responder** â†’ Mercado Pago desativa o tÃ³pico
2. **Webhook nÃ£o responde HTTP 200** â†’ NotificaÃ§Ãµes sÃ£o perdidas
3. **TÃ³pico "payments" nÃ£o estÃ¡ ativo** no DevCenter
4. **URL do webhook incorreta** ou inacessÃ­vel

---

## âœ… SoluÃ§Ã£o Implementada

### 1. OtimizaÃ§Ã£o do Webhook (`api/mercadolivre/webhook.php`)

**ANTES (Problema):**
- Processamento **sÃ­ncrono** completo
- Consulta API Mercado Pago **antes** de responder
- AtualizaÃ§Ã£o de 3 tabelas **antes** de responder
- Envio de email **antes** de responder
- â±ï¸ Tempo total: **2-5 segundos**

**DEPOIS (Otimizado):**
```php
// 1. Recebe notificaÃ§Ã£o
// 2. Valida dados bÃ¡sicos (50ms)
// 3. Adiciona Ã  fila (20ms)
// 4. RESPONDE HTTP 200 IMEDIATAMENTE (< 100ms) âœ…
// 5. Fecha conexÃ£o HTTP
// 6. Processa dados em background (assÃ­ncrono)
```

**Resultado:** âš¡ **Resposta em < 100ms** (5x mais rÃ¡pido que o limite de 500ms)

### 2. Sistema de Fila

- NotificaÃ§Ãµes sÃ£o salvas em `logs/webhook_queue.json`
- Processamento acontece **apÃ³s** resposta HTTP 200
- Usa `fastcgi_finish_request()` para fechar conexÃ£o
- Logs detalhados em `logs/webhook_mp.log`

### 3. Scripts de DiagnÃ³stico

| Script | FunÃ§Ã£o |
|--------|--------|
| `api/diagnostico/testar_webhook.php` | Simula notificaÃ§Ã£o do Mercado Pago |
| `api/diagnostico/verificar_missed_feeds.php` | Lista notificaÃ§Ãµes perdidas |
| `api/diagnostico/listar_payment_methods.php` | Lista mÃ©todos de pagamento ativos |

---

## ðŸ”§ ConfiguraÃ§Ã£o no DevCenter

### Passo 1: Acessar o DevCenter

1. Acesse: **https://developers.mercadolivre.com.br/devcenter/**
2. FaÃ§a login com sua conta do Mercado Livre
3. Clique em **"Minhas aplicaÃ§Ãµes"**
4. Selecione sua aplicaÃ§Ã£o (MovAmazon)

### Passo 2: Configurar NotificaÃ§Ãµes

1. Clique em **"Editar"** na sua aplicaÃ§Ã£o
2. Role atÃ© a seÃ§Ã£o **"TÃ³picos"**
3. **âœ… MARQUE** o tÃ³pico **"payments"**
4. No campo **"URL de retorno de notificaÃ§Ãµes"**, insira:
   ```
   https://www.movamazon.com.br/api/mercadolivre/webhook.php
   ```

5. Clique em **"Salvar"**

### Passo 3: Verificar ConfiguraÃ§Ã£o

ApÃ³s salvar, vocÃª verÃ¡:

- âœ… **TÃ³pico "payments"** marcado
- âœ… **URL do webhook** configurada
- âœ… Status: **Ativo**

**âš ï¸ IMPORTANTE:**

- A URL deve usar **HTTPS** (obrigatÃ³rio)
- A URL deve ser **acessÃ­vel publicamente**
- O servidor deve responder **HTTP 200** em **< 500ms**

---

## ðŸ§ª Testes e DiagnÃ³stico

### Teste 1: Simular NotificaÃ§Ã£o

```bash
# No servidor
php api/diagnostico/testar_webhook.php
```

**OU** acesse via navegador:
```
https://www.movamazon.com.br/api/diagnostico/testar_webhook.php
```

**O que verificar:**
- âœ… Resposta HTTP 200
- âœ… Tempo < 500ms (idealmente < 100ms)
- âœ… Log criado em `logs/webhook_mp.log`
- âœ… Entrada na fila `logs/webhook_queue.json`

### Teste 2: Verificar NotificaÃ§Ãµes Perdidas

```bash
# No servidor
php api/diagnostico/verificar_missed_feeds.php
```

**PrÃ©-requisito:** Adicione no `.env`:
```env
ML_APP_ID=SEU_APP_ID_AQUI
```

**Como obter APP_ID:**
1. DevCenter â†’ Sua aplicaÃ§Ã£o
2. Copie o **"client_id"** (esse Ã© o APP_ID)

**Resultado esperado:**
```
âœ… EXCELENTE! Nenhuma notificaÃ§Ã£o perdida!
```

**Se houver notificaÃ§Ãµes perdidas:**
```
âš ï¸ ATENÃ‡ÃƒO: 5 notificaÃ§Ã£o(Ãµes) perdida(s) encontrada(s)!
```

### Teste 3: Pagamento Real

1. FaÃ§a uma inscriÃ§Ã£o no sistema
2. Gere um PIX ou Boleto
3. Efetue o pagamento (pode ser valor mÃ­nimo R$ 5,00)
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

## ðŸ” Troubleshooting

### Problema 1: Webhook nÃ£o estÃ¡ sendo chamado

**Sintomas:**
- Pagamento aprovado no Mercado Pago
- Nenhum log em `webhook_mp.log`
- Nenhuma entrada em `php_errors.log`

**SoluÃ§Ãµes:**

1. **Verificar se o tÃ³pico estÃ¡ ativo:**
   - DevCenter â†’ AplicaÃ§Ã£o â†’ TÃ³picos
   - Confirme que "payments" estÃ¡ âœ… marcado

2. **Verificar URL do webhook:**
   - Deve ser: `https://www.movamazon.com.br/api/mercadolivre/webhook.php`
   - Teste acessando direto no navegador (deve retornar algo)

3. **Verificar firewall/hospedagem:**
   - A URL deve ser acessÃ­vel de fora
   - IPs do Mercado Pago que fazem requisiÃ§Ãµes:
     - 54.88.218.97
     - 18.215.140.160
     - 18.213.114.129
     - 18.206.34.84

4. **Reinscrever-se nos tÃ³picos:**
   - Se o webhook foi desativado por fallback, vocÃª precisa:
   - DevCenter â†’ AplicaÃ§Ã£o â†’ Desmarcar "payments"
   - Salvar
   - Marcar novamente "payments"
   - Salvar

### Problema 2: Webhook estÃ¡ sendo chamado mas nÃ£o salva no banco

**Sintomas:**
- Log em `webhook_mp.log` mostra notificaÃ§Ã£o recebida
- Dados nÃ£o aparecem na tabela `pagamentos`
- Erro em `php_errors.log`

**SoluÃ§Ãµes:**

1. **Verificar logs de erro:**
```bash
tail -100 logs/php_errors.log | grep WEBHOOK
```

2. **Verificar se external_reference existe:**
```sql
SELECT * FROM inscricoes WHERE external_reference = 'VALOR_DO_LOG';
```

3. **Verificar credenciais:**
   - Access token deve ser de **PRODUÃ‡ÃƒO**
   - Sem nenhum cÃ³digo de sandbox no sistema

### Problema 3: Resposta > 500ms

**Sintomas:**
- `verificar_missed_feeds.php` mostra notificaÃ§Ãµes com `req_time > 500`
- Webhook desativado automaticamente

**SoluÃ§Ã£o:**

1. **Aplicar webhook otimizado:**
   - FaÃ§a upload do `webhook.php` atualizado
   - VersÃ£o otimizada responde em < 100ms

2. **Otimizar servidor:**
   - PHP 8.0+ (melhor performance)
   - OPcache ativado
   - ConexÃ£o de banco rÃ¡pida

3. **Confirmar otimizaÃ§Ã£o:**
```bash
php api/diagnostico/testar_webhook.php
# Deve mostrar: âš¡ Resposta em XXms âœ… EXCELENTE (< 500ms)
```

### Problema 4: External Reference nÃ£o encontrado

**Sintomas:**
```
âš ï¸ InscriÃ§Ã£o nÃ£o encontrada: ref=INSCRIÃ‡ÃƒO_123
```

**SoluÃ§Ãµes:**

1. **Verificar se a inscriÃ§Ã£o foi salva:**
```sql
SELECT * FROM inscricoes WHERE id = 123;
-- Verificar se external_reference estÃ¡ preenchido
```

2. **Verificar fluxo de criaÃ§Ã£o:**
   - `salvar_ficha.php` deve salvar `external_reference`
   - Formato: `INSCRIÃ‡ÃƒO_[ID]`

3. **Checar logs da criaÃ§Ã£o:**
```bash
grep "INSCRIÃ‡ÃƒO_123" logs/inscricoes_pagamentos.log
```

### Problema 5: Erro ao sincronizar status / "Erro ao consultar pagamento"

**Sintomas:**
- Modal: "Erro ao sincronizar status: Erro ao consultar pagamento: Si quieres conocer los recursos..."
- Webhook nÃ£o atualiza e a sincronizaÃ§Ã£o manual tambÃ©m falha

**Causas comuns:**

1. **Access token invÃ¡lido ou expirado**
   - No `.env`, confirme `APP_Acess_token` (ou `ML_ACCESS_TOKEN_PROD`) com o token de **produÃ§Ã£o** do DevCenter.
   - Gere um novo token em DevCenter â†’ Sua aplicaÃ§Ã£o â†’ Credenciais de produÃ§Ã£o, se necessÃ¡rio.

2. **Webhook nÃ£o estÃ¡ sendo chamado**
   - O payment_id já é registrado na criação (PIX/Boleto/Cartão) em pagamentos_ml com status pendente/processando.
   - O webhook é quem confirma/atualiza o status para pago e completa dados finais.
   - Se não houver payment_id em pagamentos_ml, investigar falha nos endpoints de criação (create_pix.php, create_boleto.php, process_payment_preference.php) ou erro de banco.
   - A rotina de sync usa `payment_id` ou, na falta dele, `external_reference`. Desde a correÃ§Ã£o no `PaymentHelper`, quando sÃ³ hÃ¡ `external_reference` (ex: MOVAMAZON_27), o sistema **busca o pagamento na API do MP por external_reference** e depois consulta o status. Se a busca nÃ£o retornar nada, verifique se a URL do webhook estÃ¡ correta e acessÃ­vel (veja Problema 1).

3. **URL do webhook no .env e no DevCenter**
   - `.env`: `ML_NOTIFICATION_URL=https://www.movamazon.com.br/api/mercadolivre/webhook.php`
   - DevCenter â†’ AplicaÃ§Ã£o â†’ URL de retorno de notificaÃ§Ãµes: **exatamente** a mesma URL, com HTTPS.
   - Teste: acesse a URL no navegador; deve responder (nÃ£o 404).

4. **Assinatura do webhook (x-signature)**
   - Se no `.env` estiver configurado `ML_WEBHOOK_SECRET`, o webhook valida o header `x-signature`. O valor deve ser o mesmo configurado no painel do Mercado Pago para a aplicaÃ§Ã£o. Se estiver errado, o webhook rejeita e retorna 200 sem processar (veja logs).

**O que foi corrigido no cÃ³digo:**
- `api/mercadolivre/payment_helper.php`: quando o valor passado nÃ£o Ã© um ID numÃ©rico (ex: MOVAMAZON_27), o helper chama a API de **search** do Mercado Pago por `external_reference`, obtÃ©m o `payment_id` real (priorizando o mais recente) e depois consulta o pagamento. Assim a sincronizaÃ§Ã£o manual funciona mesmo sem webhook.

---

## ðŸ“Š Monitoramento ContÃ­nuo

### Logs Importantes

| Arquivo | ConteÃºdo |
|---------|----------|
| `logs/webhook_mp.log` | Todas as notificaÃ§Ãµes recebidas |
| `logs/inscricoes_pagamentos.log` | Processamento detalhado |
| `logs/php_errors.log` | Erros crÃ­ticos |
| `logs/webhook_queue.json` | Fila de processamento |

### Comandos Ãšteis

```bash
# Ver Ãºltimas notificaÃ§Ãµes
tail -20 logs/webhook_mp.log

# Monitorar em tempo real
tail -f logs/webhook_mp.log

# Buscar payment especÃ­fico
grep "payment_id_123" logs/webhook_mp.log

# Ver erros do webhook
grep WEBHOOK logs/php_errors.log

# Ver fila atual
cat logs/webhook_queue.json | python -m json.tool
```

### MÃ©tricas Esperadas

âœ… **Sistema saudÃ¡vel:**
- Tempo de resposta: < 100ms
- HTTP Status: 200
- NotificaÃ§Ãµes perdidas: 0
- Taxa de sucesso: 100%

âš ï¸ **AtenÃ§Ã£o necessÃ¡ria:**
- Tempo de resposta: 100-500ms
- NotificaÃ§Ãµes perdidas: 1-5
- Erros ocasionais em `php_errors.log`

âŒ **Problema crÃ­tico:**
- Tempo de resposta: > 500ms
- HTTP Status: 500, 400
- NotificaÃ§Ãµes perdidas: > 5
- Webhook desativado

---

## ðŸ“ Checklist PÃ³s-ImplementaÃ§Ã£o

ApÃ³s fazer upload dos arquivos:

- [ ] Webhook otimizado (`webhook.php`) enviado para hospedagem
- [ ] Scripts de diagnÃ³stico enviados (`api/diagnostico/*.php`)
- [ ] DevCenter configurado (tÃ³pico "payments" ativo)
- [ ] URL do webhook configurada corretamente
- [ ] `.env` com `ML_APP_ID` configurado
- [ ] Teste manual executado (`testar_webhook.php`)
- [ ] Verified missed_feeds (`verificar_missed_feeds.php`)
- [ ] Pagamento real testado e confirmado
- [ ] Logs monitorados por 24h
- [ ] DocumentaÃ§Ã£o lida e compreendida

---

## ðŸ†˜ Suporte

Se apÃ³s seguir todos os passos o problema persistir:

1. **Colete informaÃ§Ãµes:**
   ```bash
   # Executar diagnÃ³sticos
   php api/diagnostico/testar_webhook.php > diagnostico.txt
   php api/diagnostico/verificar_missed_feeds.php >> diagnostico.txt
   
   # Logs recentes
   tail -100 logs/webhook_mp.log >> diagnostico.txt
   tail -100 logs/php_errors.log >> diagnostico.txt
   ```

2. **Verificar configuraÃ§Ã£o:**
   - Screenshot do DevCenter mostrando tÃ³picos ativos
   - URL do webhook configurada
   - Resultado do teste manual

3. **Contatar suporte do Mercado Pago:**
   - Discord: Mercado Pago Developers
   - Email: developers@mercadopago.com
   - DocumentaÃ§Ã£o: https://www.mercadopago.com.br/developers

---

## ðŸ“š ReferÃªncias

- [DocumentaÃ§Ã£o oficial de NotificaÃ§Ãµes](https://developers.mercadolivre.com.br/pt_br/produto-receba-notificacoes)
- [Criar aplicaÃ§Ã£o no Mercado Livre](https://developers.mercadolivre.com.br/pt_br/crie-uma-aplicacao-no-mercado-livre)
- [Gerenciamento de Pagamentos](https://developers.mercadolivre.com.br/pt_br/gerenciamento-de-pagamentos)
- [Status do Sistema](https://status.mercadopago.com/)

---

**Ãšltima atualizaÃ§Ã£o:** 30/01/2026  
**VersÃ£o do sistema:** 2.0 (Webhook Otimizado)

