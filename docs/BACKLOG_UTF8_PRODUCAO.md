# Backlog Operacional UTF-8 (Execucao)

Data: 2026-02-24
Referencia: `docs/PLANO_EXECUTIVO_UTF8_PRODUCAO.md`

Escala de esforco:
- P: ate 0.5 dia
- M: 1 dia
- G: 2 dias

## Sprint 1 - Saneamento Base

1. `UTF8-001` | Prioridade P0 | Esforco M
- Tarefa: criar helper unico para respostas JSON UTF-8 e adotar nos endpoints de maior trafego.
- Dependencia: nenhuma.
- Concluido quando: endpoints criticos respondem com `application/json; charset=utf-8`.

2. `UTF8-002` | Prioridade P0 | Esforco P
- Tarefa: padronizar headers HTML com UTF-8.
- Dependencia: nenhuma.
- Concluido quando: paginas principais sem divergencia de charset no response header.

3. `UTF8-010` | Prioridade P0 | Esforco P
- Tarefa: gerar inventario de arquivos com marcadores de mojibake.
- Dependencia: nenhuma.
- Concluido quando: lista versionada em docs com baseline inicial.

4. `UTF8-011A` | Prioridade P0 | Esforco G
- Tarefa: corrigir modulo `frontend/paginas/inscricao` + `frontend/js/inscricao`.
- Dependencia: UTF8-010.
- Concluido quando: zero mojibake visual no fluxo de inscricao e pagamento.

5. `UTF8-011B` | Prioridade P0 | Esforco M
- Tarefa: corrigir `frontend/js/participante/pagamento-inscricao.js` e telas relacionadas.
- Dependencia: UTF8-010.
- Concluido quando: textos de PIX/Boleto 100% legiveis.

## Sprint 2 - API e Dominio

1. `UTF8-001B` | Prioridade P0 | Esforco G
- Tarefa: padronizar charset em todos os endpoints restantes de `api/`.
- Dependencia: UTF8-001.
- Concluido quando: scanner retorna 0 endpoint JSON sem charset.

2. `UTF8-012A` | Prioridade P1 | Esforco M
- Tarefa: corrigir mensagens de erro visiveis e templates de email.
- Dependencia: UTF8-001B.
- Concluido quando: smoke test de erros e emails sem texto corrompido.

3. `UTF8-030` | Prioridade P1 | Esforco M
- Tarefa: auditar integracoes (webhook, Mercado Pago, CSV/PDF).
- Dependencia: UTF8-001B.
- Concluido quando: payloads e arquivos exportados preservam acentos.

## Sprint 3 - Banco de Dados

1. `UTF8-020` | Prioridade P0 | Esforco P
- Tarefa: auditoria de charset/collation do schema.
- Dependencia: nenhuma.
- Concluido quando: relatorio de colunas fora de `utf8mb4`.

2. `UTF8-021` | Prioridade P0 | Esforco M
- Tarefa: migration para normalizar schema textual.
- Dependencia: UTF8-020.
- Concluido quando: 100% tabelas/colunas textuais em `utf8mb4`.

3. `UTF8-022` | Prioridade P0 | Esforco M
- Tarefa: detectar dados ja corrompidos nas tabelas criticas.
- Dependencia: UTF8-020.
- Concluido quando: dataset candidato para reparo aprovado.

4. `UTF8-023` | Prioridade P0 | Esforco G
- Tarefa: executar reparo com dry-run, snapshot e rollback testado.
- Dependencia: UTF8-022.
- Concluido quando: amostras validas e sem perda funcional.

## Sprint 4 - Blindagem (Sem Regressao)

1. `UTF8-040` | Prioridade P0 | Esforco P
- Tarefa: CI gate para marcadores de mojibake.
- Dependencia: nenhuma.
- Concluido quando: pipeline falha ao detectar padroes proibidos.

2. `UTF8-041` | Prioridade P0 | Esforco P
- Tarefa: CI gate para endpoint JSON sem charset.
- Dependencia: UTF8-001B.
- Concluido quando: regra obrigatoria ativa no PR.

3. `UTF8-042` | Prioridade P1 | Esforco M
- Tarefa: smoke tests automatizados com strings acentuadas.
- Dependencia: UTF8-040.
- Concluido quando: suite executa em CI com evidencias.

4. `UTF8-013` | Prioridade P1 | Esforco M
- Tarefa: remover fallback runtime de correcao (`corrigirMojibake`) apos 2 releases estaveis.
- Dependencia: UTF8-042.
- Concluido quando: fallback removido sem regressao.

## Sprint 5 - Rollout Producao

1. `UTF8-050` | Prioridade P0 | Esforco P
- Tarefa: deploy por ondas (staging -> canario -> full).
- Dependencia: Sprints 1-4.
- Concluido quando: canario aprovado por metrica.

2. `UTF8-051` | Prioridade P0 | Esforco P
- Tarefa: monitor de logs para marcadores de mojibake + alerta.
- Dependencia: UTF8-050.
- Concluido quando: alerta testado em ambiente real.

3. `UTF8-052` | Prioridade P0 | Esforco P
- Tarefa: ensaio de rollback codigo + dados.
- Dependencia: UTF8-050.
- Concluido quando: runbook validado pelo time.

## Metricas de Fechamento

- M1: `files_com_mojibake_markers = 0` (escopo de producao).
- M2: `json_headers_sem_charset = 0`.
- M3: `runtime_fallbacks = 0` (apos janela de estabilizacao).
- M4: `incidentes_utf8 = 0` por 7 dias apos rollout full.

