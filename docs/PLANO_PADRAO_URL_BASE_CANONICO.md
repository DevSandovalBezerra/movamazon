# Plano de Padrao Canonico de URL Base

Data: 2026-02-20  
Status: executado (fase critica)

## Decisao oficial

Adotar um unico padrao de URL base no sistema:

- Backend canônico: `api/helpers/url_base.php`
  - `app_url_base()`
  - `app_api_url()`
  - `app_asset_url()`
- Frontend canônico: `frontend/js/core/url-base.js`
  - `buildApiUrl(path)`
  - `buildAssetUrl(path)`
  - `window.URL_BASE` como fonte principal quando injetado pelo backend

## Regras obrigatorias

1. Se `URL_BASE` estiver definido, ele e soberano.
2. Sem montagem manual de base por pagina/rota.
3. E proibido novo `window.API_BASE =` fora do modulo canônico.
4. Em Mercado Livre e fluxos criticos, URLs devem derivar do helper canônico.

## Super warning (protecao de contexto critico)

Arquivos com contexto critico:

- `api/helpers/url_base.php`
- `frontend/js/core/url-base.js`
- `api/mercadolivre/config.php`

Diretriz:

- Nao alterar regras de URL base nesses arquivos sem aprovacao explicita (ADR/ticket).
- Mudancas nesses arquivos exigem justificativa no commit/PR.

## Bloqueio automatico de regressao

Implementar checagem automatica para falhar quando houver:

- `window.API_BASE =` fora de `frontend/js/core/url-base.js`
- uso de `REQUEST_URI` ou `SCRIPT_NAME` para montar base URL em configs criticas
- montagem manual de `/api/` em modulos que ja usam `buildApiUrl`

Script de validacao:

- `scripts/check_url_base_patterns.php`

Uso:

- executar em pre-commit/CI antes de merge.

## Ordem de execucao

1. Consolidar `frontend/js/core/url-base.js` como fonte unica.
2. Injetar `window.URL_BASE` via headers PHP compartilhados.
3. Alinhar `api/mercadolivre/config.php` ao helper canônico com fallback seguro.
4. Migrar modulos criticos para `buildApiUrl`/`buildAssetUrl`.
5. Ativar checagem automatica e regra de bloqueio.

## Procedimentos realizados

1. **Nucleo canônico de URL no frontend**
   - Atualizado `frontend/js/core/url-base.js` com:
     - priorizacao de `window.URL_BASE` / `window.APP_BASE`;
     - fallback deterministico sem heuristica por rota;
     - marcacao canônica `window.__URL_BASE_CANONICAL__ = true`;
     - comentario de super warning no topo.

2. **Injecao de `URL_BASE` no HTML compartilhado**
   - Atualizados:
     - `frontend/includes/header.php`
     - `frontend/includes/header_index.php`
   - Ambos agora resolvem `app_url_base()` via `api/helpers/url_base.php` e expõem `window.URL_BASE`.

3. **Alinhamento de Mercado Livre ao helper canônico**
   - Atualizado `api/mercadolivre/config.php`:
     - uso prioritario de `URL_BASE`;
     - fallback por `app_url_base()` quando `URL_BASE` nao estiver setado;
     - fallback legado mantido somente como protecao operacional;
     - super warning adicionado no topo.

4. **Blindagem de regressao**
   - Criada regra em `.cursor/rules/padrao-url-base-canonico.mdc`.
   - Criado script `scripts/check_url_base_patterns.php` para bloquear:
     - novo `window.API_BASE =` fora do arquivo canônico;
     - uso indevido de `REQUEST_URI`/`SCRIPT_NAME` para base URL fora dos arquivos permitidos.

5. **Correcao do incidente de producao (UTF-8 e renderizacao)**
   - Reescritos em UTF-8 limpo:
     - `frontend/js/components/eventCard.js`
     - `frontend/js/ui/counter.js`
   - Ajustes de robustez:
     - `api/evento/list_public.php` com `charset=utf-8` e `JSON_UNESCAPED_UNICODE`;
     - `api/banners/public.php` valida existencia de arquivo de banner;
     - `frontend/js/public/banners.js` com fallback seguro quando imagem nao existir;
     - `frontend/js/api/eventos.js` migrado para `buildApiUrl`.

6. **Mitigacao de cache de modulos ES**
   - Atualizado `frontend/paginas/public/index.php` com cache-busting por `filemtime` para:
     - `../../js/public/banners.js`
     - `../../js/main.js`
   - Ajustado `frontend/js/components/eventCard.js` para import namespace de `formatters.js` com fallback seguro de `corrigirMojibake`.
