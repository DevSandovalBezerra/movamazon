# Revisao profissional do PRD - Modulo Financeiro Movamazon

Atualizado em: 23/02/2026

## Veredito geral

**Aprovado com ressalvas, parcialmente ja enderecadas na implementacao.**

O PRD original continua bom como direcao de produto e arquitetura (ledger imutavel, rastreabilidade, separacao de escopo MVP/Fase 2).  
A revisao anterior estava tecnicamente correta para o texto do PRD, mas ficou desatualizada em alguns pontos porque o projeto evoluiu.

---

## O que continua correto na revisao (sobre o PRD original)

1. **Lacuna na secao 6.2 do PRD**  
   No PRD, a secao "6.2 Indices, AUTO_INCREMENT e FKs" afirma que estao incluidos, mas o SQL exibido ali nao traz esse detalhamento completo.

2. **Numeracao incompleta no PRD**  
   O documento pula da secao 6 para a 9, sem secoes 7 e 8 formalizadas.

3. **Secao 11 do PRD esta placeholder**  
   O texto cita "Implementacao PHP" sem detalhar contratos e estrutura.

---

## O que a revisao anterior marcou como "falta", mas JA foi implementado

1. **Migration completa com PK/AUTO_INCREMENT/indices/FKs**  
   Ja existe em `migrations/2026-02-23_financeiro_modulo.sql`.

2. **Endpoints com autenticacao/autorizacao**  
   Ja aplicados em `api/financeiro/extrato_listar.php`, `api/financeiro/repasse_agendar.php`, `api/financeiro/repasse_processar.php`, `api/financeiro/estorno_registrar.php`, `api/financeiro/chargeback_registrar.php`, `api/financeiro/fechamento_gerar.php`.

3. **Vinculo explicito ledger <-> repasse**  
   Ja implementado no service com lancamentos de bloqueio, liberacao e saida efetiva em `api/financeiro/financeiro_service.php`.

4. **Regra de receita bruta/liquida**  
   Ja implementada com base em `lotes_inscricao.quem_paga_taxa`, registrada em metadata no ledger.

5. **Uso de `disponivel_em` (D+N / money_release_date)**  
   Ja implementado com `money_release_date` do gateway e fallback por configuracao de prazo do evento.

6. **Chargeback com impacto em ledger**  
   Ja implementado com debito/bloqueio e reversao quando status for ganho.

7. **Fechamento com snapshot read-only**  
   Ja implementado em `financeiro_fechamentos` com snapshot JSON e IDs considerados.

8. **Idempotencia de webhook + registro de receita/taxa no ledger**  
   Ja implementado na integracao com `api/mercadolivre/webhook.php` + funcoes do `api/financeiro/financeiro_service.php`.

---

## Pendencias reais (estado atual)

1. **Alinhar documentacao para evitar divergencia**  
   Atualizar `docs/modulo_financeiro_prd_plano.md` para refletir o que foi implementado (ou manter como PRD "produto" e criar um "Documento Tecnico de Referencia" separado e oficial).

2. **Fechar politica operacional de reabertura de fechamento**  
   A implementacao ja gera snapshot; falta formalizar regra de negocio de reabertura (quem pode, quando pode e efeitos em auditoria).

3. **Evolucoes de fase 2 continuam validas**  
   Multi-moeda, split e conciliacao automatica permanecem como backlog, sem bloquear o MVP.

---

## Matriz de status (revisao x estado atual)

| Item | Avaliacao hoje |
|------|-----------------|
| PRD e objetivos | Aprovado |
| Arquitetura ledger | Aprovado |
| UX proposta (cards/extrato/filtros) | Aprovado |
| Lacunas textuais no PRD (6.2, 7-8, 11) | Procede |
| "Falta migration completa" | **Nao procede mais** (ja implementado) |
| "Falta auth nos endpoints financeiro" | **Nao procede mais** (ja implementado) |
| "Repasse nao refletido no ledger" | **Nao procede mais** (ja implementado) |
| "Chargeback sem impacto no ledger" | **Nao procede mais** (ja implementado) |
| "Snapshot de fechamento ausente" | **Nao procede mais** (ja implementado) |

---

## Conclusao

As correcoes propostas na revisao fazem sentido como criterio tecnico, mas o documento precisa ser lido agora como **historico parcial**: varias recomendacoes ja foram entregues no codigo e nas migrations.  

O caminho recomendado e manter o PRD como visao de produto e publicar um documento tecnico atualizado como fonte unica da implementacao vigente.
