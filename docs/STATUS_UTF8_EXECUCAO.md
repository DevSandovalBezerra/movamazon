# Status de Execucao UTF-8 (Producao)

Data: 2026-02-24
Escopo: correcao de mojibake/UTF-8 no sistema MovAmazon

## 1) O que foi feito

1. Correcao de textos com mojibake em arquivos criticos de frontend:
- `frontend/js/inscricao/pagamento.js`
- `frontend/js/participante/pagamento-inscricao.js`
- `frontend/js/admin/solicitacoes.js`
- `frontend/js/categorias.js`
- `frontend/js/mercadopago-config.js`
- `frontend/js/participante/treinos.js`

2. Auditoria UTF-8 fortalecida:
- `scripts/utf8_audit.ps1` com leitura UTF-8 estrita.
- Deteccao refinada para reduzir falso-positivo.
- Metrica `invalid_utf8_files` adicionada.

3. Automacao de correcao residual:
- `scripts/fix_utf8_batch.js` revisado para operacao por fases e modo seguro.
- `scripts/fix_remaining_mojibake.js` criado/ajustado para limpeza residual.

4. Gate de CI atualizado:
- Workflow falha se houver:
  - JSON sem charset
  - HTML sem charset
  - arquivo invalido UTF-8

5. Correcao operacional de erro 500:
- Ajuste de compatibilidade Apache 2.4+ em:
  - `api/.htaccess`
  - `frontend/paginas/panel/.htaccess`
- Sintoma corrigido: `Invalid command 'Order'` gerando `500`.

6. Remocao de fallback runtime de encoding:
- Removidos `corrigirMojibake`, `TextDecoder`, `countMojibakeMarkers` do fluxo ativo.
- Arquivos principais:
  - `frontend/js/utils/formatters.js`
  - `frontend/js/components/eventCard.js`

7. Smoke test automatizado + CI:
- Script criado: `scripts/smoke_test.ps1`.
- Atalho local criado: `npm run smoke:test`.
- CI integrado no workflow existente: `.github/workflows/phpunit.yml`.
- O smoke test roda junto no pipeline de pre-deploy em push/PR.
- Artefato de relatorio JSON publicado no CI.
- Guia criado: `docs/SMOKE_TEST_AUTOMATICO.md`.
- Guia de deploy resumido criado: `docs/GUIA_DEPLOY_RESUMIDO_ULTIMAS_ALTERACOES.md`.

## 2) Metricas atuais (local)

- `json_headers_without_charset=0`
- `html_headers_without_charset=0`
- `files_with_mojibake_markers=0`
- `invalid_utf8_files=0`
- `runtime_fallback_markers=0`

## 3) O que falta fazer

1. Executar bateria final de testes funcionais logados:
- inscricao
- pagamento
- dashboard participante
- area admin/organizador

2. Configurar secrets no GitHub para smoke autenticado no CI:
- `SMOKE_EMAIL`
- `SMOKE_PASSWORD`

3. Validar em staging/producao com rollout controlado (canario) e monitoramento pos-deploy.

## 4) Estimativa restante

- Conclusao tecnica local: concluida para UTF-8 e gate automatizado.
- Fechamento com seguranca de producao (staging/canario): `0.5 a 1.5 dias uteis`.

## 5) Criterio de encerramento

1. Auditoria UTF-8 com zero pendencias estruturais.
2. Zero regressao em fluxos criticos.
3. Nenhum erro 500 no dashboard relacionado ao problema.
4. Fallbacks runtime de encoding removidos.
5. Smoke test no CI executando sem falhas (publico e autenticado).
