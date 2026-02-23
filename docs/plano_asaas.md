# PRD + Prompt de Desenvolvimento (Cursor AI) — Pagamentos Asaas com Split + Retenção (Eventos de Corrida)

Versão: 1.0  
Stack alvo: PHP 8.2+, MySQL/MariaDB, PDO, Composer, Nginx/Apache  
Objetivo: Implementar cobrança (Pix/Cartão) com split de 3 partes: comissão (7%), organizador liberado (63%), organizador retido (30%) com liberação no fechamento do evento.

---

## 1) Contexto e Visão

Plataforma de gerenciamento de corridas no Brasil.  
Atletas pagam inscrição. A plataforma:
- retém comissão de 7%
- repassa automaticamente 63% ao organizador
- retém 30% (garantia) até o fechamento do evento, liberando depois

O split ocorre no momento da cobrança via API Asaas. O saldo retido fica em uma carteira/conta Asaas separada (subconta “Garantia”), e é transferido ao organizador quando o evento for fechado.

---

## 2) Objetivos (Goals)

1. Cobrar inscrições via Pix e Cartão.
2. Criar cobrança no Asaas e aplicar split 3-way.
3. Persistir ledger interno com rastreabilidade por evento/inscrição.
4. Processar webhooks do Asaas (status de pagamento/estorno/disputa).
5. Implementar fechamento do evento e liberação do saldo retido via transferências Asaas.
6. Conciliação contábil por evento com relatório reproduzível.

---

## 3) Não-objetivos (Non-Goals)

- Antifraude avançada (3DS, score) no MVP.
- Parcelamento complexo e múltiplos adquirentes.
- Motor de chargeback com evidências automatizadas (ter só o básico).
- Multi-moeda.

---

## 4) Premissas e Regras de Negócio

- Comissão da plataforma: 7% (recomendação: valor fixo calculado por cobrança para garantir 7% do bruto; ver seção 9).
- Organizador: 93% do bruto.
- Retenção: 30% da parte do organizador (idealmente sobre o líquido do organizador pós-taxa, por consistência contábil).
- Retenção é por evento (evento tem data de fechamento definida).
- Cada organizador precisa ter conta Asaas (walletId) para receber split.
- Deve existir uma conta Asaas “Garantia” sob controle da plataforma (walletId de garantia).

---

## 5) Fluxo do Usuário (UX)

### 5.1 Atleta
1. Escolhe evento/lote
2. Seleciona Pix ou Cartão
3. Confirma dados
4. Paga
5. Recebe confirmação e comprovante/status no painel

### 5.2 Organizador
- Visualiza total vendido, comissão, taxas, liberado, retido, estornos
- Solicita/acompanha fechamento do evento (admin da plataforma aprova)

### 5.3 Admin da plataforma
- Gerencia eventos e organizadores
- Configura walletId do organizador e vinculações
- Fecha evento e libera retenção
- Acompanha conciliação e divergências

---

## 6) Integrações Asaas (MVP)

### 6.1 Autenticação
- API Key em variável de ambiente (não em código)
- Separar SANDBOX e PROD
- Timeout curto e retry com idempotência

### 6.2 Endpoints principais (referência conceitual)
- Customers: criar/atualizar pagador
- Payments: criar cobrança (Pix/cartão)
- Webhooks: receber eventos
- Transfers: transferir da conta Garantia para organizador no fechamento
- Listar transfers/payments para conciliação

---

## 7) Modelo de Dados (MySQL) — Mínimo necessário

### 7.1 Tabelas

#### organizers
- id (PK)
- name
- document (CPF/CNPJ)
- asaas_wallet_id (string)
- status (active/inactive)
- created_at, updated_at

#### events
- id (PK)
- organizer_id (FK)
- name
- starts_at
- ends_at
- close_at (datetime planejado)
- closed_at (datetime real)
- status (draft/open/closed)
- guarantee_wallet_id (string)  // opcional: por evento; ou global
- created_at, updated_at

#### athletes
- id (PK)
- name
- cpf
- email
- phone
- created_at, updated_at

#### registrations
- id (PK)
- event_id (FK)
- athlete_id (FK)
- lot_id (opcional)
- amount_gross (decimal 10,2)
- currency (BRL)
- status (pending/paid/canceled/refunded/disputed)
- created_at, updated_at

#### asaas_payments
- id (PK)
- registration_id (FK)
- asaas_payment_id (string unique)
- billing_type (PIX/CREDIT_CARD/BOLETO)
- status (asaas status)
- amount_gross
- fee_amount (decimal 10,2) // quando conhecido
- net_amount (decimal 10,2) // quando conhecido
- created_at, updated_at
- raw_payload (json) // opcional, para auditoria

#### ledger_entries
- id (PK)
- event_id (FK)
- registration_id (FK)
- asaas_payment_id (string)
- entry_type (PAYMENT_CONFIRMED / REFUND / CHARGEBACK / TRANSFER_OUT / TRANSFER_IN / ADJUSTMENT)
- amount_gross
- amount_fee
- amount_net
- split_platform
- split_organizer_released
- split_guarantee_held
- occurred_at
- created_at

#### event_settlement
- id (PK)
- event_id (FK)
- gross_total
- fee_total
- platform_total
- organizer_released_total
- guarantee_held_total
- refunds_total
- chargebacks_total
- to_release_from_guarantee
- settled_at
- created_at

#### asaas_transfers
- id (PK)
- event_id (FK)
- organizer_id (FK)
- asaas_transfer_id (string unique)
- amount
- status
- created_at, updated_at
- raw_payload (json)

---

## 8) Cálculo do Split (Regra Técnica)

### 8.1 Alvos do split
- walletId_platform
- walletId_organizer
- walletId_guarantee (sob controle da plataforma)

### 8.2 Percentuais alvo (conceituais)
- plataforma: 7%
- organizador liberado: 63%
- garantia retida: 30%

### 8.3 Importante (base de cálculo)
Se o split percentual for aplicado sobre líquido (após taxas), sua comissão real deixa de ser 7% do bruto.
MVP recomendado:
- Calcular valores fixos de split por cobrança no backend (em centavos), baseado no bruto.
- Registrar no ledger os valores exatos enviados no split.

---

## 9) Estratégia de Taxas (Contábil + Técnica)

### 9.1 Quem paga as taxas do gateway
Recomendação MVP:
- Taxas “do meio de pagamento” impactam o organizador (reduz risco de margem negativa na plataforma).

Como implementar:
- Opção A: split calculado sobre bruto e depois conciliar taxa como custo do organizador no ledger (mais simples comercialmente, exige transparência).
- Opção B: split ajustado para descontar taxa da parte do organizador (mais correto contábil, exige conhecer fee cedo; pode variar).

MVP pragmático:
- Registrar fee posteriormente (quando webhook/consulta retornar taxa real).
- Em relatórios: exibir valores “bruto”, “taxa”, “líquido” por parte.

---

## 10) Fluxos Técnicos (Sequências)

### 10.1 Criar inscrição + cobrança Pix
1. POST /api/registrations (event_id, athlete data, billing=PIX)
2. Upsert athlete
3. Create registration status=pending
4. Create/Upsert Asaas customer
5. Create Asaas payment PIX com split 3-way
6. Persist asaas_payment_id
7. Retornar QR code / payload pix para frontend
8. Frontend exibe QR + atualiza status consultando endpoint /status ou por SSE/polling

### 10.2 Criar cobrança cartão
Igual ao Pix, mas billing=CREDIT_CARD e coleta dados do cartão via forma segura (ideal: tokenização/checkout do provedor; no MVP, usar o método suportado com cuidado de PCI).

### 10.3 Webhook: pagamento confirmado
1. Validar assinatura/origem (ver seção 11)
2. Identificar asaas_payment_id
3. Buscar registration vinculada
4. Se já confirmado, ignorar (idempotência)
5. Marcar registration=paid
6. Inserir ledger entry PAYMENT_CONFIRMED com:
   - bruto
   - fee (se disponível)
   - splits efetivos
7. Atualizar asaas_payments status + net/fee

### 10.4 Webhook: estorno/refund
1. Marcar registration=refunded (ou canceled)
2. Inserir ledger entry REFUND (valores negativos)
3. Ajustar saldos agregados do evento (em relatório, não “editando” histórico)

### 10.5 Fechamento do evento (liberação da garantia)
1. Admin aciona “Fechar evento”
2. Sistema calcula:
   - saldo retido (somatório ledger split_guarantee_held - refunds/chargebacks relacionados)
3. Cria transferências Asaas:
   - from: walletId_guarantee
   - to: walletId_organizer
4. Persistir asaas_transfer_id e status
5. Marcar evento closed_at
6. Gerar event_settlement consolidado

---

## 11) Segurança, Idempotência e Auditoria

### 11.1 Webhooks
- Validar secret/token de webhook
- Processar em fila (job) para resiliência
- Idempotência por:
  - asaas_payment_id + evento webhook id (se existir)
  - trava transacional no DB (unique key + status check)

### 11.2 Idempotência nas chamadas Asaas
- Ao criar pagamento: guardar idempotency_key por registration_id e reutilizar em retry
- Nunca duplicar cobrança se timeout

### 11.3 Logs e trilhas
- Guardar payload bruto do webhook (raw_payload) com redaction de dados sensíveis
- Guardar trilha de mudanças de status (opcional: tabela status_history)

---

## 12) Casos de Borda (Edge Cases)

- Atleta paga Pix após expiração: tratar como falha e gerar nova cobrança.
- Duplo webhook (reentrega): idempotência.
- Chargeback no cartão após evento fechado: criar ajuste (ADJUSTMENT) e refletir em relatório do organizador; política contratual precisa cobrir isso.
- Organizador sem walletId: bloquear abertura do evento ou bloqueio de vendas até completar.
- Evento cancelado: definir regra de estorno em massa (job).

---

## 13) Testes (Checklist)

### Unit
- Cálculo split fixo (centavos), soma = bruto
- Cálculo de retenção e liberado
- Geração do settlement por evento

### Integração
- Criar customer + payment pix com split
- Processar webhook confirmado
- Fechar evento e gerar transfers
- Conciliação: comparar somas do ledger vs settlement

### E2E
- Jornada atleta Pix (pagar e confirmar)
- Jornada atleta cartão (pagar e confirmar)
- Jornada admin fechar evento e liberar retenção

---

## 14) Rollout (Implantação)

1. Ambiente SANDBOX end-to-end
2. Feature flags:
   - pagamentos_pix
   - pagamentos_cartao
   - split_3way
   - fechamento_evento
3. Produção com 1 evento piloto (100–300 atletas)
4. Observabilidade:
   - taxa de conversão pix/cartão
   - falhas criação cobrança
   - tempo médio confirmação pix
   - divergências conciliação

---

# 15) Prompt de Desenvolvimento (Cursor AI) — Tarefas Guiadas

Use os prompts abaixo em sequência no Cursor.  
Regra: sempre usar PHP + PDO + transações (begin/commit/rollback) nas gravações críticas.

---

## Prompt 1 — Estrutura base (DB + migrations)
Você é um desenvolvedor sênior em PHP. Crie migrations SQL para MySQL com as tabelas: organizers, events, athletes, registrations, asaas_payments, ledger_entries, event_settlement, asaas_transfers.  
Requisitos:
- Campos conforme PRD
- Índices: asaas_payment_id unique, asaas_transfer_id unique
- FKs coerentes
- Decimals com 2 casas
Entregue:
- arquivo db/schema.sql
- arquivo db/migrate.php simples para aplicar schema (PDO)

---

## Prompt 2 — Cliente HTTP Asaas (PHP)
Implemente um client HTTP em PHP:
- class AsaasClient
- métodos: createCustomer, createPaymentPix, createPaymentCard, getPayment, createTransfer
- suporte a baseUrl por ambiente
- header com apiKey
- tratamento de erros HTTP
- timeouts
- método request($method, $path, $payload=null)
Entregue em src/AsaasClient.php.

---

## Prompt 3 — Serviço de Split (cálculo em centavos)
Implemente SplitCalculator:
Entrada: amount_gross (decimal), commission_pct=0.07, hold_pct_of_organizer=0.30  
Saída:
- platform_amount
- organizer_released_amount
- guarantee_held_amount
- organizer_total
Regras:
- trabalhar em centavos (inteiros) para evitar erro de ponto flutuante
- soma final deve ser exatamente igual ao bruto em centavos
- regra de arredondamento: ajustar diferença no guarantee_held_amount
Entregue em src/SplitCalculator.php + testes simples.

---

## Prompt 4 — Endpoint criar inscrição + cobrança Pix
Crie endpoint POST /api/registrations/pix:
Entrada: event_id, athlete {name, cpf, email, phone}, amount=129.00  
Processo:
- transação DB
- upsert athlete
- create registration pending
- criar customer Asaas (ou reutilizar por cpf/email)
- calcular split e criar payment Pix no Asaas com split 3-way
- salvar asaas_payment_id
- retornar qrCode/pix payload para frontend
Requisitos:
- logar request/response (sem dados sensíveis)
- idempotência: se já existe cobrança para registration, retorne a existente
Entregue:
- public/index.php router simples
- src/Controllers/RegistrationController.php
- src/Repositories/*

---

## Prompt 5 — Webhook handler (idempotente)
Crie endpoint POST /webhooks/asaas:
- validar secret via header X-Webhook-Token (comparar com env)
- parse payload
- mapear por asaas_payment_id
- atualizar status e gerar ledger_entries
- garantir idempotência:
  - se status já confirmado e chegou confirmado de novo, ignore
- usar transações
Entregue:
- src/Controllers/WebhookController.php
- src/Services/PaymentStatusService.php

---

## Prompt 6 — Fechamento do evento + liberação de garantia
Crie endpoint POST /api/events/{id}/close:
- checa permissões (placeholder)
- calcula total retido no evento via ledger (sum split_guarantee_held - refunds/chargebacks)
- cria transferência Asaas (guarantee_wallet -> organizer_wallet) no valor calculado
- grava asaas_transfers e ledger entry TRANSFER_OUT
- marca event.closed_at e status=closed
- gera event_settlement
Entregue:
- src/Controllers/EventController.php
- src/Services/EventSettlementService.php

---

## Prompt 7 — Relatório contábil por evento (CSV/JSON)
Crie endpoint GET /api/events/{id}/settlement:
Retornar:
- bruto total
- taxas total (se houver)
- comissão total
- organizador liberado total
- garantia retida total
- estornos total
- chargebacks total
- a liberar (se em aberto)
Formato:
- JSON + opção CSV (?format=csv)
Entregue:
- src/Controllers/ReportController.php

---

## Prompt 8 — Rotina de conciliação (job)
Crie script cli/php bin/reconcile.php:
- lista pagamentos do evento no DB
- para os pagos, consulta Asaas getPayment e atualiza fee/net se mudou
- registra ADJUSTMENT no ledger se houver divergência
- gera log do reconciliado
Entregue em bin/reconcile.php.

---

## 16) Critérios de Aceite (Definition of Done)

- Criar cobrança Pix com split 3-way funcionando em sandbox
- Webhook confirma e atualiza inscrição para paid
- Ledger registra valores do split
- Fechamento do evento cria transferência e settlement
- Relatório do evento bate com somatório do ledger
- Idempotência evita duplicidade em webhook e criação de cobrança

---

## 17) Variáveis de Ambiente

- ASAAS_ENV=sandbox|prod
- ASAAS_API_KEY=...
- ASAAS_BASE_URL=...
- ASAAS_WEBHOOK_TOKEN=...
- WALLET_PLATFORM_ID=...
- WALLET_GUARANTEE_ID=...
- DB_DSN=mysql:host=...;dbname=...
- DB_USER=...
- DB_PASS=...

---

## 18) Observações finais (pragmáticas)

- Split percentual pode não refletir exatamente 7% do bruto dependendo da base de cálculo; no MVP prefira split por valor fixo em centavos.
- Retenção por evento é mais fácil com subconta/carteira Garantia e transfer no fechamento.
- Para cartão, evite tocar em dados sensíveis; prefira checkout/tokenização do provedor quando disponível.

Fim.
