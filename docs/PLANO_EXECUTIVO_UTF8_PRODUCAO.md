# Plano Executivo UTF-8 Producao (Sistema Completo)

Data de referencia: 2026-02-24
Escopo: backend, frontend, banco, integracoes, deploy e CI
Status: em execucao (correcao de codigo concluida; rollout operacional pendente)

## 1) Objetivo

Eliminar definitivamente qualquer mojibake no sistema e impedir regressao.

Definicao de pronto:
- Nenhuma tela publica ou autenticada com texto corrompido.
- Nenhum endpoint JSON sem `charset=utf-8`.
- Nenhum fluxo critico dependente de "correcao em runtime" de texto quebrado.
- Pipeline bloqueando novos arquivos/textos com padrao de mojibake.

## 2) Baseline Atual (local)

Levantamento local em 2026-02-24 (antes -> atual):
- `0` endpoints com `Content-Type: application/json` sem charset explicito.
- `0` respostas HTML sem `charset=UTF-8`.
- `39 -> 0` arquivos com marcadores de mojibake (auditoria UTF-8 estrita).
- `0` arquivos invalidos em UTF-8.
- `14 -> 0` ocorrencias de fallback runtime para tentar reparar encoding no frontend.

Observacao:
- Esse baseline e local. Antes do corte em producao, rodar o mesmo auditor em staging/prod.

## 3) Principios Tecnicos

- Correcao na origem, nao mascaramento no browser.
- Uma unica politica de encoding para entrada, processamento e saida.
- Mudanca incremental com gate de qualidade e rollback simples.
- Deploy canario antes de abertura total.

## 4) Workstreams e Backlog

### WS1 - Padrao de Transporte HTTP

- UTF8-001: Criar bootstrap unico para API com header padrao `application/json; charset=utf-8`.
  - Esforco: 1 dia
  - Saida: helper central + adocao progressiva nos endpoints.
- UTF8-002: Padronizar respostas HTML `text/html; charset=UTF-8` onde aplicavel.
  - Esforco: 0.5 dia
- UTF8-003: Garantir configuracao Apache/PHP para UTF-8 default (sem depender apenas de meta tag).
  - Esforco: 0.5 dia

### WS2 - Higienizacao de Codigo Fonte

- UTF8-010: Inventariar todos os arquivos com marcadores de mojibake (api/frontend/src/scripts).
  - Esforco: 0.5 dia
- UTF8-011: Corrigir arquivos com texto visivel ao usuario (prioridade: inscricao/pagamento/dashboard).
  - Esforco: 2 dias
- UTF8-012: Corrigir mensagens de erro, logs funcionais e templates de email.
  - Esforco: 1 dia
- UTF8-013: Remover gradualmente fallback de runtime (`corrigirMojibake`) apos estabilizacao.
  - Esforco: 1 dia

### WS3 - Banco de Dados (schema + dados)

- UTF8-020: Auditoria de charset/collation no schema inteiro (`utf8mb4`).
  - Esforco: 0.5 dia
- UTF8-021: Migracoes para alinhar tabelas/colunas fora do padrao.
  - Esforco: 1 dia
- UTF8-022: Auditoria de dados corrompidos por tabela critica (usuarios, eventos, modalidades, kits, mensagens).
  - Esforco: 1 dia
- UTF8-023: Script de reparo em lote com dry-run + relatorio + rollback por snapshot.
  - Esforco: 1.5 dia

### WS4 - Fronteiras Externas

- UTF8-030: Validar webhooks, pagamentos, exportacoes CSV/PDF e email para UTF-8 consistente.
  - Esforco: 1 dia
- UTF8-031: Revisar serializacao JSON (`JSON_UNESCAPED_UNICODE` quando apropriado) e logs.
  - Esforco: 0.5 dia

### WS5 - Qualidade e Gate de Regressao

- UTF8-040: Criar job de CI para falhar em marcadores de mojibake.
  - Esforco: 0.5 dia
- UTF8-041: Criar regra de CI para impedir endpoint JSON novo/alterado sem charset.
  - Esforco: 0.5 dia
- UTF8-042: Testes de fumaca em fluxos criticos com strings acentuadas.
  - Esforco: 1 dia

### WS6 - Deploy, Canario e Operacao

- UTF8-050: Plano de deploy por ondas (staging -> canario -> 100%).
  - Esforco: 0.5 dia
- UTF8-051: Monitoramento de logs por padrao de mojibake + alerta.
  - Esforco: 0.5 dia
- UTF8-052: Runbook de rollback (codigo + dados) testado.
  - Esforco: 0.5 dia

## 5) Cronograma Sugerido (12 dias uteis)

- Fase A (Dias 1-2): baseline, scripts de auditoria, WS1 inicio.
- Fase B (Dias 3-5): correcoes WS2 em codigo visivel + API principal.
- Fase C (Dias 6-8): WS3 banco (auditoria, dry-run, correcao controlada).
- Fase D (Dias 9-10): WS4 e estabilizacao funcional.
- Fase E (Dias 11-12): WS5/WS6, canario e fechamento.

## 6) Criterios de Aceite

- CA-01: `0` telas criticas com mojibake em checklist funcional.
- CA-02: `100%` endpoints JSON com `charset=utf-8`.
- CA-03: `100%` schema textual em `utf8mb4`.
- CA-04: `0` ocorrencia nova em CI para marcadores de mojibake.
- CA-05: fallback runtime desativado sem regressao por 2 releases.

## 7) Testes Obrigatorios

- Fluxo inscricao completo: modalidade -> termos -> ficha -> pagamento -> sucesso.
- Fluxo participante: dashboard, inscricoes, perfil, pagamentos.
- Fluxo admin/organizador: listagens, CRUDs, relatorios principais.
- Integracoes: webhook pagamento, notificacoes email, exportacao CSV.
- Banco: consultas amostrais antes/depois do reparo.

## 8) Riscos e Mitigacoes

- Risco: correcao automatica alterar texto valido.
  - Mitigacao: dry-run, diff por lote, revisao manual de amostras.
- Risco: dado historico irreversivel.
  - Mitigacao: snapshot e rollback por tabela antes de atualizar dados.
- Risco: reintroducao por novo commit.
  - Mitigacao: gate de CI obrigatorio.
- Risco: cache/CDN servir arquivo antigo.
  - Mitigacao: invalidacao de cache e verificacao por hash.

## 9) Politica de Rollback

- Codigo:
  - rollback da release via pacote anterior.
- Dados:
  - restore de snapshot da tabela afetada (nao restore global sem necessidade).
- Criterio de acionamento:
  - qualquer regressao em fluxo critico com impacto em UX/comercial.

## 10) Ordem de Execucao Recomendada

1. Fechar WS1 para padrao de resposta.
2. Executar WS2 com foco em textos de alto impacto.
3. Executar WS3 com dry-run e janela controlada.
4. Fechar WS4 para fronteiras externas.
5. Entrar em WS5/WS6 e abrir canario.

## 11) Entregaveis Formais

- Relatorio baseline (antes/depois).
- Lista de arquivos corrigidos e endpoints padronizados.
- Evidencia de testes funcionais e de CI.
- Runbook final de operacao e rollback.
