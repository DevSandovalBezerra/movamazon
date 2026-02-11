# Como averiguar a resposta do webhook (Mercado Pago)

Quando um pagamento PIX (ou outro) é feito e a tela de pagamento não atualiza sozinha, use estes passos para verificar se o webhook recebeu e processou a notificação.

---

## 1. Logs do webhook

### 1.1 Log principal (processamento)
- **Arquivo:** `logs/webhook_mp.log`
- **Conteúdo:** Cada notificação recebida e resultado do processamento (payment_id, status, external_reference, “Inscrição não encontrada”, etc.).
- **Como ver:** Abra o arquivo no servidor ou via FTP. As últimas linhas mostram os eventos mais recentes.

### 1.2 Retorno gravado (payload recebido)
- **Arquivo:** `api/mercadolivre/webhook_retorno.txt`
- **Conteúdo:** Uma linha por notificação com timestamp e JSON de retorno (status ok/error, payment_id, etc.).
- **Como ver:** Abra o arquivo. Confira se há linhas com `"status":"ok"` e o `payment_id` do pagamento em questão.

### 1.3 Log do PHP (erros e debug)
- **Onde:** `error_log` do PHP (em hospedagem: painel → Log de erros / php_errors.log).
- **Procure por:** `[WEBHOOK]` — entradas de início, recebimento, “Inscrição não encontrada”, “Inscrição X: pendente → pago”, etc.

---

## 2. Diagnóstico via navegador (organizador/admin)

- **URL:** `https://seu-dominio.com.br/api/mercadolivre/diagnostico_webhook.php`
- **Requisito:** Usuário logado como **organizador** ou **admin**.
- **O que mostra:** Configuração (URL do webhook, tokens), existência de tabelas, últimas linhas do `webhook_mp.log`, recomendações.
- **Uso:** Confirme se a URL de notificação está correta e se os logs existem e foram atualizados após o pagamento.

---

## 3. Tabela de logs de inscrições/pagamentos

- **Tabela:** `logs_inscricoes_pagamentos`
- **Consulta exemplo (últimos eventos de um payment_id):**
```sql
SELECT id, nivel, acao, inscricao_id, payment_id, status_pagamento, mensagem, created_at
FROM logs_inscricoes_pagamentos
WHERE payment_id = 'ID_DO_PAGAMENTO_MP'
   OR inscricao_id = 13
ORDER BY created_at DESC
LIMIT 20;
```
- **O que ver:** Se existe `STATUS_ATUALIZADO` ou `WEBHOOK_PROCESSAMENTO` com status `pago` após o pagamento. Se aparecer `INSCRICAO_NAO_ENCONTRADA_WEBHOOK`, o webhook recebeu a notificação mas não encontrou a inscrição (veja a seção “Problema comum” abaixo).

---

## 4. Status da inscrição no banco

- **Tabela:** `inscricoes`
- **Consulta (ex.: inscrição 13):**
```sql
SELECT id, usuario_id, evento_id, status, status_pagamento, forma_pagamento, data_pagamento, external_reference, data_inscricao
FROM inscricoes
WHERE id = 13;
```
- **Se o PIX foi confirmado e o webhook funcionou:** `status` = `confirmada`, `status_pagamento` = `pago`, `data_pagamento` preenchida, `forma_pagamento` = `pix` (ou o que o webhook definir).

---

## 5. Fila do webhook (processamento assíncrono)

- **Arquivo:** `logs/webhook_queue.json`
- **Conteúdo:** Fila de notificações recebidas (payment_id, data, IP). O processamento em background consome essa fila.
- **Uso:** Ver se a notificação do pagamento em questão entrou na fila (presença do payment_id correto).

---

## 6. Resumo rápido (checklist)

| Onde | O que verificar |
|------|------------------|
| `logs/webhook_mp.log` | Entrada com o payment_id e linha do tipo “Inscrição X: pendente → pago” ou “Inscrição não encontrada” |
| `api/mercadolivre/webhook_retorno.txt` | Linha com status ok e o mesmo payment_id |
| `logs_inscricoes_pagamentos` | Ação `STATUS_ATUALIZADO` ou `WEBHOOK_PROCESSAMENTO` para a inscrição/payment_id |
| `inscricoes` | `status_pagamento = 'pago'` e `data_pagamento` preenchida para a inscrição |
| `diagnostico_webhook.php` | URL do webhook correta e logs existentes/atualizados |

---

## Problema comum: “Inscrição não encontrada” no webhook (PIX)

No fluxo PIX, após gerar o pagamento o sistema grava o **payment_id** do Mercado Pago em `inscricoes.external_reference`. O webhook, porém, busca a inscrição pelo `external_reference` que vem **no payload do pagamento** (ex.: `MOVAMAZON_13`). Se no banco já tiver sido salvo o payment_id, a busca por `MOVAMAZON_13` não encontra a inscrição.

**Correção aplicada no código:** o webhook passa a tentar encontrar a inscrição também por `external_reference = payment_id` (além do valor vindo do Mercado Pago). Assim, mesmo após o create_pix sobrescrever o `external_reference` com o payment_id, o webhook consegue localizar a inscrição e atualizar o status para pago.

---

## Teste manual do webhook (opcional)

- **Script:** `api/diagnostico/testar_webhook.php` (se existir no projeto).
- **Uso:** Simular uma notificação com um payment_id conhecido e conferir no log e na tabela `inscricoes` se o status foi atualizado.

Com isso você consegue averiguar a resposta do webhook e o motivo de a tela de pagamento não ter atualizado (por exemplo, webhook não chamado, inscrição não encontrada ou atraso no processamento assíncrono).

---

## O que o Mercado Pago exige (documentação oficial)

- **URL pública e acessível:** A URL de notificação deve ser **HTTPS** e acessível pela internet (sem firewall bloqueando o Mercado Pago).
- **Resposta rápida:** O servidor deve responder **HTTP 200 ou 201** em até **22 segundos**. Se não confirmar, o MP reenvia a notificação (a cada ~15 min). Responder rápido evita retentativas e desativação do tópico.
- **Credenciais de produção:** Pagamentos criados com **credenciais de teste** **não disparam notificação**. Só há webhook quando o pagamento é criado com credenciais de **produção**.
- **Prioridade da URL:** Se a `notification_url` foi enviada na criação do pagamento (como no nosso `create_pix`), essa URL é usada. Configuração em "Suas integrações" no painel do desenvolvedor é alternativa/fallback.

---

## Quando o webhook não é chamado (não aparece nada nos logs)

Se não há nenhuma entrada em `webhook_mp.log`, `webhook_retorno.txt` ou `logs_inscricoes_pagamentos` para o payment_id em questão:

1. **Credenciais de teste:** Confirmar no `.env` / painel que está usando **Access Token e Public Key de produção**. Em teste, o MP não envia notificação.
2. **URL inacessível:** A URL configurada em `ML_NOTIFICATION_URL` (ou o fallback `https://www.movamazon.com.br/api/mercadolivre/webhook.php`) deve responder em HTTPS e ser acessível de fora (testar em outra rede ou com ferramenta online). Firewall ou WAF não podem bloquear requisições do Mercado Pago.
3. **Painel do desenvolvedor:** Em [Mercado Pago Developers](https://www.mercadopago.com.br/developers) → Sua integração → Webhooks (ou Notificações), verificar se há histórico de notificações enviadas e se alguma falhou (status de entrega). Isso ajuda a saber se o MP tentou chamar a URL e recebeu erro/timeout.
4. **Simulador:** Usar o simulador de notificações no painel do MP para enviar um evento de pagamento de teste e confirmar que a URL recebe a requisição e responde 200.

Depois de garantir que a URL é acessível e que está em produção, as correções no código (buscar inscrição também por `external_reference = payment_id`) garantem que, quando o webhook for chamado, a inscrição seja encontrada e o status atualizado.
