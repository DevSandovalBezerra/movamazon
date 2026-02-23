# Guia de Deploy - Modulo Financeiro

## 1) Escopo implementado

- Migration completa do financeiro com PK, AUTO_INCREMENT, indices e FKs:
  - `migrations/2026-02-23_financeiro_modulo.sql`
- Service financeiro expandido com contratos operacionais:
  - `api/financeiro/financeiro_service.php`
- Endpoints protegidos por autenticacao + autorizacao de organizador:
  - `api/financeiro/extrato_listar.php`
  - `api/financeiro/repasse_agendar.php`
  - `api/financeiro/repasse_processar.php`
  - `api/financeiro/fechamento_gerar.php`
  - `api/financeiro/estorno_registrar.php`
  - `api/financeiro/chargeback_registrar.php`
- Integracao do webhook Mercado Pago com:
  - idempotencia em `financeiro_webhook_eventos`
  - lancamento de receita/taxa no ledger
  - arquivo: `api/mercadolivre/webhook.php`

## 2) Ordem de deploy

1. Executar SQL:
   - `migrations/2026-02-23_financeiro_modulo.sql`
2. Publicar arquivos PHP:
   - `api/financeiro/*.php`
   - `api/mercadolivre/webhook.php`
3. Validar endpoints com usuario organizador autenticado.

## 3) Contratos principais do service

- `fin_repasse_agendar(PDO $pdo, int $evento_id, int $beneficiario_id, int $conta_id, $valor_in, string $agendar_para, int $user_id = 0): array`
- `fin_repasse_processar(PDO $pdo, int $repasse_id, string $novo_status, int $user_id = 0, ?string $comprovante_url = null, ?string $gateway_transfer_id = null, ?string $motivo_falha = null): array`
- `fin_registrar_receita_pagamento(PDO $pdo, int $inscricao_id, ?string $payment_id = null, ?array $payment_data = null, int $user_id = 0): array`
- `fin_estorno_registrar(...)` e `fin_chargeback_registrar(...)`
- `fin_fechamento_gerar(PDO $pdo, int $evento_id, int $fechado_por = 0): array`
- `fin_webhook_registrar_evento(PDO $pdo, string $gateway, string $event_id, ?string $payment_id, ?string $status, $payload): bool`

## 4) Regras tecnicas aplicadas

- Ledger imutavel:
  - sem editar linha antiga para ajustar saldo
  - compensacoes por novos lancamentos
- Repasse:
  - agendamento gera debito `bloqueado`
  - pagamento gera dupla de compensacao: credito de liberacao + debito de saida
  - falha/cancelamento gera credito de desbloqueio
- Receita:
  - webhook aprovado registra credito de pagamento e debito de taxa gateway
  - `disponivel_em` usa `money_release_date` do gateway quando existir, senao fallback D+1
- Regra bruta/liquida:
  - inferida por evento via `lotes_inscricao.quem_paga_taxa`
  - registrada em metadata para auditoria
- Idempotencia webhook:
  - evento gravado em `financeiro_webhook_eventos` (gateway + event_id unico)
  - duplicados sao ignorados antes de alterar tabelas de negocio

## 5) Checklist rapido pos-deploy

- [ ] Tabelas `financeiro_*` criadas com FKs e indices.
- [ ] `GET /api/financeiro/extrato_listar.php?evento_id=...` retorna 200 para organizador do evento.
- [ ] `POST /api/financeiro/repasse_agendar.php` bloqueia saldo no ledger.
- [ ] `POST /api/financeiro/repasse_processar.php` gera lancamentos de compensacao esperados.
- [ ] Webhook aprovado gera lancamento `origem_tipo=pagamento` e `origem_tipo=taxa`.
- [ ] Reenvio do mesmo webhook e marcado como duplicado e nao duplica lancamento.
