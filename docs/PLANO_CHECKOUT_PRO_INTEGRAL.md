# Plano: Implementação integral com Checkout Pro (100%)

Objetivo: adotar **apenas** Checkout Pro (redirect para o Mercado Pago) em todo o sistema, removendo ambiguidade e maximizando a personalização possível da experiência.

---

## 1. Escopo

| Fluxo | Situação atual | Meta |
|-------|----------------|------|
| **Inscrição (etapa 4)** | Já usa redirect (botão → create_preference → init_point). Código de Brick/PIX/Boleto ainda presente no JS, não usado. | Manter redirect; remover ou inativar código morto (Brick/PIX/Boleto); reforçar personalização. |
| **Área do participante (pagamento-inscricao)** | Usa Payment Brick + PIX + Boleto na página. | Trocar por Checkout Pro: um botão "Pagar com Mercado Pago" → create_preference → redirect para init_point. Remover Brick e botões PIX/Boleto. |
| **Retorno do pagamento** | Inscrição: /inscricao/sucesso. Participante: back_urls apontam para um único conjunto (hoje ML_AUTO_RETURN = inscricao/sucesso). | back_urls **por contexto**: inscrição → /inscricao/sucesso; participante → /participante/pagamento-sucesso (e pendente/erro). |

---

## 2. Backend

### 2.1 Preferência: back_urls por contexto

- **Arquivo:** `api/inscricao/create_preference.php`
- **Alteração:** Aceitar parâmetro opcional `origem` no JSON: `"inscricao"` | `"participante"` (default: `"inscricao"`).
- **Lógica:** Construir `back_urls` conforme a origem, usando a base do config:
  - **inscricao:**  
    - success: `{base}/inscricao/sucesso` (ou com `?status=approved`)  
    - pending: `{base}/inscricao/sucesso?status=pending`  
    - failure: `{base}/inscricao/sucesso?status=failure`
  - **participante:**  
    - success: `{base}/frontend/paginas/participante/pagamento-sucesso.php`  
    - pending: `{base}/frontend/paginas/participante/pagamento-pendente.php`  
    - failure: `{base}/frontend/paginas/participante/pagamento-erro.php`
- **Base:** Usar variável do config (ex.: `production_base` ou nova chave `site_base_url`) para montar as URLs completas.
- **Personalização (já feita / manter):** `items` com `picture_url` do config, `additional_info` com nome do evento, `payer`, `statement_descriptor`.

### 2.2 Config (api/mercadolivre/config.php)

- Garantir uma única base (ex.: `site_base_url` ou uso de `production_base`) para montar as três back_urls por contexto no create_preference.
- Manter `back_urls` no return do config apenas como fallback ou remover do return e construir sempre dentro do create_preference conforme `origem`.

### 2.3 create_pix / create_boleto

- **Não remover** os endpoints (podem ser usados por outros fluxos ou diagnóstico).
- No frontend: **não chamar** mais create_pix/create_boleto nos fluxos de inscrição e de pagamento da inscrição (participante). Ou seja: remoção de uso, não de API.

---

## 3. Frontend – Inscrição (etapa 4)

### 3.1 pagamento.php

- Manter como está: resumo da compra, botão "Finalizar Compra", texto "Você será redirecionado ao Mercado Pago...".
- **Não** adicionar container do Wallet Brick (evitar CSP e manter fluxo único: redirect).
- Garantir que não exista `paymentBrick_container` nem botões PIX/Boleto na página (já está assim).

### 3.2 pagamento.js

- **Fluxo principal (manter):** Listener do "Finalizar Compra" → validar dados → `criarPreference(inscricaoId, total)` com payload que inclua `origem: 'inscricao'` → receber `init_point` → `window.location.href = init_point`.
- **criarPreference:** Incluir no body do POST o campo `origem: 'inscricao'` para o backend montar as back_urls da inscrição.
- **Código a inativar (deixar comentado ou não chamado):**
  - Chamadas a `inicializarMercadoPago` para renderizar Brick (ou manter apenas se no futuro quiser Wallet Brick; hoje não usar).
  - `renderPaymentBrick`, `inicializarPagamento` (ou manter apenas o trecho de fallback que redireciona quando não há container e há init_point – hoje o botão já redireciona direto, então esse fallback pode ficar inativo).
  - Listeners/handlers dos botões PIX e Boleto (se ainda existirem referências).
- **Objetivo:** Um único caminho ativo: botão → criarPreference(origem inscricao) → redirect; resto comentado ou morto para rollback futuro.

### 3.3 Personalização (etapa 4)

- Reforçar na **nossa** página: título claro ("Finalizar inscrição"), resumo (evento, modalidades, valor, taxa se houver), botão destacado ("Finalizar Compra" / "Ir ao pagamento"), texto explicando o redirect.
- Não alterar layout/CSS além do necessário para clareza (respeitando regra do usuário).

---

## 4. Frontend – Área do participante (pagamento da inscrição)

### 4.1 pagamento-inscricao.php

- **Remover ou ocultar:** Container `paymentBrick_container`, seção "Pagamento Instantâneo" (botão PIX), seção "Pagamento com Boleto" (botão Boleto), container do Status Screen Brick.
- **Manter:** Resumo da inscrição, valores, dados do usuário, um único CTA: **"Pagar com Mercado Pago"**.
- **Texto:** Algo como "Você será redirecionado ao Mercado Pago para pagar com cartão, PIX ou boleto."
- **Comportamento:** Ao clicar em "Pagar com Mercado Pago" → chamar create_preference com `origem: 'participante'` → redirecionar para `init_point`. Sem abrir janela de Brick.

### 4.2 pagamento-inscricao.js

- **Novo fluxo:** Um único botão "Pagar com Mercado Pago":
  1. Carregar dados da inscrição (já existe).
  2. Chamar `criarPreference(inscricaoId, valorTotal, ...)` passando no body `origem: 'participante'`.
  3. Backend retornar `preference_id` e `init_point` (create_preference já retorna init_point; no participante o JS hoje pode usar só preference_id – garantir que a API retorne também init_point e que o front use `window.location.href = init_point`).
  4. Redirecionar: `window.location.href = result.init_point`.
- **Remover / inativar:** `renderPaymentBrick`, `renderStatusScreenBrick`, `iniciarPagamentoComBrick` (abrir janela e renderizar Brick). Handlers de "Pagar com PIX" e "Pagar com Boleto".
- **Manter (comentado ou em bloco não usado):** Funções de inicialização do SDK e criarPreference para possível rollback; não chamadas no fluxo principal.

---

## 5. Retorno do pagamento (back_urls)

### 5.1 Inscrição

- **success:** `/inscricao/sucesso` (ou com query do MP: `external_reference`, `collection_status`, etc.).  
- **pending / failure:** Mesmo path com `?status=pending` e `?status=failure` (ou páginas separadas se preferir; o sucesso.php atual já trata status).
- Garantir que `frontend/paginas/inscricao/sucesso.php` trate `external_reference`, `collection_status`/`status` e exiba mensagem adequada para aprovado, pendente e falha. Regras no `.htaccess` para `/inscricao/sucesso` já existem.

### 5.2 Participante

- **success:** `frontend/paginas/participante/pagamento-sucesso.php` (com query do MP).
- **pending:** `pagamento-pendente.php`
- **failure:** `pagamento-erro.php`
- Garantir que essas páginas leiam `external_reference` e, se necessário, `payment_id`/`status` para exibir o estado correto (já compatível com Checkout Pro).

---

## 6. API create_preference: retorno

- Garantir que a resposta sempre inclua `init_point` além de `preference_id`, para ambos os fluxos (inscricao e participante). O frontend participante deve usar `init_point` para o redirect.

---

## 7. Checklist de implementação (ordem sugerida)

1. **Backend**
   - [x] Em `config.php`: definir base URL (ou usar `production_base`) para montar back_urls.
   - [x] Em `create_preference.php`: ler `origem` (inscricao|participante), montar `back_urls` por origem, manter personalização (picture_url, additional_info).
   - [x] Garantir retorno com `init_point` sempre presente.

2. **Inscrição (etapa 4)**
   - [x] Em `pagamento.js`: enviar `origem: 'inscricao'` no POST de criarPreference; manter só o fluxo de redirect no botão; comentar/inativar renderPaymentBrick, inicializarPagamento (e PIX/Boleto) sem remover o código.
   - [x] Revisar `pagamento.php`: sem container Brick, sem botões PIX/Boleto; texto de redirect claro.

3. **Participante**
   - [x] Em `pagamento-inscricao.php`: remover/ocultar `paymentBrick_container`, blocos PIX e Boleto, Status Screen; deixar um único botão "Pagar com Mercado Pago" e texto de redirect.
   - [x] Em `pagamento-inscricao.js`: fluxo único: criarPreference com `origem: 'participante'` → redirect para `init_point`; comentar/inativar renderPaymentBrick, renderStatusScreenBrick, handlers PIX/Boleto.

4. **Retorno**
   - [x] Testar inscrição: aprovar/pendente/falha → /inscricao/sucesso com status correto.
   - [x] Testar participante: aprovar/pendente/falha → páginas de sucesso/pendente/erro do participante.

5. **Personalização**
   - [x] Revisar `create_preference`: items com `picture_url` (config), `additional_info`, `payer`, `statement_descriptor`.
   - [x] Revisar textos e resumo nas páginas de pagamento (inscricao e participante) para experiência clara e alinhada à marca.

---

## 8. Rollback (voltar ao Brick/PIX/Boleto)

- **Backend:** Em `create_preference.php`, remover o uso de `$origem` e voltar a usar `$config['back_urls']` direto. Em `config.php`, as chaves `back_urls_inscricao` e `back_urls_participante` podem permanecer.
- **Inscrição:** Em `frontend/js/inscricao/pagamento.js`, definir `USE_CHECKOUT_PRO_REDIRECT = false`. Reative os listeners de PIX/Boleto (já dentro de `if (!USE_CHECKOUT_PRO_REDIRECT)`). Na página de pagamento, exiba o container `paymentBrick_container` e faça o botão chamar `inicializarPagamento()` em vez de só redirect.
- **Participante:** Em `frontend/js/participante/pagamento-inscricao.js`, definir `USE_CHECKOUT_PRO_REDIRECT = false`. Em `frontend/paginas/participante/pagamento-inscricao.php`, remover a classe `hidden` de `#janela-pagamento-mercadopago`. O `iniciarPagamento()` já chama `iniciarPagamentoComBrick` quando a flag é false.

## 9. Referências

- Plano comparativo: `checkout_pro_vs_sistema_atual_a45b49fb.plan.md`
- Exemplo doc: `docs/exemplo chechoupro.md`
- Personalização Checkout Pro: `docs/CHECKOUT_PRO_PERSONALIZACAO.md`
- Backend: `api/inscricao/create_preference.php`, `api/mercadolivre/config.php`
- Inscrição: `frontend/paginas/inscricao/pagamento.php`, `frontend/js/inscricao/pagamento.js`, `frontend/paginas/inscricao/sucesso.php`
- Participante: `frontend/paginas/participante/pagamento-inscricao.php`, `frontend/js/participante/pagamento-inscricao.js`, `pagamento-sucesso.php`, `pagamento-pendente.php`, `pagamento-erro.php`
