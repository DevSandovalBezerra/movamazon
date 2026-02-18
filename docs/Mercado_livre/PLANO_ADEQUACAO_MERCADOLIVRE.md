# Plano de adequação às exigências Mercado Livre/Mercado Pago

Este plano foi elaborado com base em:
- `docs/Mercolivre_exigencias.md`
- análise do diretório `api/mercadolivre`
- documentação oficial do Mercado Pago (integração, SDKs e qualidade)

## Diagnóstico rápido

**Já existe:**
- SDK JS v2 carregado no frontend (`frontend/paginas/inscricao/pagamento.php`).
- Itens com `id` e `category_id` em preferences (`api/inscricao/create_preference.php`).
- Itens também no fluxo de `process_payment_preference.php` (quando há `inscricao_id`).

**Faltas apontadas pela plataforma:**
- Uso de **SDK de backend** (hoje usa cURL direto).
- `statement_descriptor` (fatura do cartão).
- Melhorias de qualidade/segurança recomendadas pela página de integração.

---

## Objetivos

1. Migrar integrações do backend para **SDK oficial** (mercadopago/dx-php).
2. Inserir `statement_descriptor` nos pagamentos/preferências.
3. Consolidar itens e metadados conforme recomendações.
4. Garantir configuração correta de `notification_url`, `back_urls` e segurança.
5. Melhorar o índice de qualidade da integração no painel MP.

---

## Plano detalhado

### Fase 1 — Checklist e validação dos requisitos

1. Revisar `docs/Mercolivre_exigencias.md` e listar exigências em um checklist com status.
2. Conferir as páginas oficiais:
   - Qualidade da integração (Checkout Pro)
   - SDK JS v2
   - SDK backend
   - `statement_descriptor`
3. Validar quais pontos já estão atendidos no código e quais precisam de ajustes.

### Fase 2 — SDK JS v2 (frontend)

1. Confirmar o uso do SDK JS v2 em todas as telas de pagamento.
2. Verificar se `device_id` (MP_DEVICE_SESSION_ID) está sendo enviado ao backend.
3. Garantir que o frontend não exponha tokens privados.

**Arquivos:**  
- `frontend/paginas/inscricao/pagamento.php`  
- `frontend/js/inscricao/pagamento.js`

### Fase 3 — SDK Backend (PHP)

1. Instalar SDK: `composer require mercadopago/dx-php`.
2. Criar um wrapper único (ex.: `api/mercadolivre/MercadoPagoClient.php`):
   - inicialização do SDK
   - criação de preferência
   - pagamentos diretos (PIX/Boleto/Cartão)
   - consulta de pagamento
3. Substituir cURL nos arquivos que fazem chamadas diretas:
   - `api/inscricao/create_preference.php`
   - `api/inscricao/process_payment_preference.php`
   - `api/inscricao/create_pix.php`
   - `api/inscricao/create_boleto.php`
   - `api/mercadolivre/webhook.php`
   - `api/inscricao/get_payment_status.php`
   - `api/mercadolivre/MercadoLivrePayment.php`

### Fase 4 — statement_descriptor

1. Definir um valor padrão (ex.: `MOVAMAZON`).
2. Adicionar em:
   - Preferences (Checkout Pro).
   - Pagamentos diretos (`/v1/payments`).
3. Validar limites de tamanho e caracteres conforme documentação.

### Fase 5 — Itens e metadados

1. Garantir `items.id`, `items.title`, `items.category_id` em todas as preferences.
2. Para pagamentos diretos:
   - Confirmar se a API aceita `items` (em alguns casos não aceita).
   - Se não aceitar, manter itens no metadata e nos registros internos.
3. Padronizar `category_id` (ex.: `EBL-Evento Desportivo`).

### Fase 6 — Configurações de segurança

1. Validar `notification_url` (`ML_NOTIFICATION_URL`) com HTTPS e domínio correto.
2. Validar `back_urls` (success/pending/failure).
3. Conferir uso de `ML_WEBHOOK_SECRET` e assinatura no webhook.
4. Revisar logs de webhook para erros recorrentes.

### Fase 7 — Validação manual

1. Criar pagamentos com:
   - Cartão
   - PIX
   - Boleto
2. Confirmar:
   - `statement_descriptor` aparece na fatura.
   - `items.id`/`category_id` enviados em preferences.
   - Webhook recebe e grava em `pagamentos_ml`.
   - Retorno e fallback funcionam.

---

## Arquivos impactados (estimados)

- `api/inscricao/create_preference.php`
- `api/inscricao/process_payment_preference.php`
- `api/inscricao/create_pix.php`
- `api/inscricao/create_boleto.php`
- `api/mercadolivre/webhook.php`
- `api/inscricao/get_payment_status.php`
- `api/mercadolivre/MercadoLivrePayment.php`
- (novo) `api/mercadolivre/MercadoPagoClient.php`

---

## Observações

- Confirmar compatibilidade do PHP com `mercadopago/dx-php`.
- `statement_descriptor` deve seguir as regras do MP (tamanho e caracteres).
- Ajustes no backend devem ser feitos com cuidado para não quebrar o fluxo atual.
