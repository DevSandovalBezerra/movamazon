# Plano de Correcao - Normalizacao de URL Base em Producao

Data: 2026-02-18
Status: aprovado para execucao

## 1) Problema confirmado

Temos quebra recorrente de URLs por montagem manual e inconsistente de base path.
No caso analisado (termos da inscricao), o log mostra chamada para:

- `https://www.movamazon.com.br/paginas/api/inscricao/get_termos.php?...`

Essa URL esta incorreta (prefixo `/paginas/api`), retornou conteudo nao-JSON e causou:

- `erro_termos_gerais: "Erro desconhecido"`
- `erro_modalidade_25: "Tipo nao e modalidade"`

Tambem foi confirmado no historico Git que a ultima alteracao direta de `URL_BASE` ocorreu no commit `c7ab4c9`, em `api/mercadolivre/config.php`, migrando de base fixa para `URL_BASE`/base dinamica.

## 2) Causa raiz

1. Nao existe um resolvedor unico de URL base para todo o sistema.
2. Existem multiplas regras locais para montar base (`window.API_BASE`, `getApiUrl`, `getBaseUrl`) com heuristicas diferentes.
3. Alguns fluxos inferem base pela `REQUEST_URI`/`pathname` e acabam promovendo segmentos de pagina (`/paginas`) para base da API.
4. A validacao de resposta JSON nao bloqueia erro de contrato cedo; o erro "muda de lugar" por fallback.

## 3) Objetivo de correcao

Padronizar 100% da construcao de URL (API e assets) com uma unica regra, orientada por `URL_BASE`, com fallback deterministico, eliminando heuristica por pagina.

## 4) Plano de implementacao

## Fase 0 - Hotfix imediato (prioridade critica)

1. Corrigir `frontend/paginas/inscricao/termos.php` para:
- usar `URL_BASE` quando existir.
- fallback para origem + prefixo de app, sem usar primeiro segmento de `REQUEST_URI`.
- montar API sempre como `BASE + /api/...`.
2. Logar no debug o `url_base_resolvido` e `http_code` da chamada.
3. Tratar `json_decode` com `json_last_error_msg()` para erro explicito.

## Fase 1 - Normalizacao backend (fonte unica)

1. Criar helper unico: `api/helpers/url_base.php`.
2. Expor funcoes:
- `app_url_base(): string`
- `app_api_url(string $path): string`
- `app_asset_url(string $path): string`
3. Regra:
- prioridade 1: `URL_BASE` do ambiente.
- prioridade 2: deteccao por host + subpasta do projeto.
- sem depender de rota atual de pagina (`/paginas`, `/frontend`, etc).
4. Migrar consumidores criticos:
- `frontend/paginas/inscricao/termos.php`
- `frontend/paginas/inscricao/modalidade.php`
- qualquer PHP que monte URL absoluta de imagem/regulamento/API.

## Fase 2 - Normalizacao frontend (fonte unica)

1. Criar modulo global `frontend/js/core/url-base.js`.
2. Definir:
- `window.APP_BASE`
- `window.API_BASE = APP_BASE + '/api'` (ou `'' + '/api'` quando raiz)
3. Consumidores passam a usar funcoes unicas:
- `buildApiUrl(path)`
- `buildAssetUrl(path)`
4. Remover inicializadores duplicados de `window.API_BASE` nos arquivos de pagina/modulo.

## Fase 3 - Imagens (ponto sensivel)

1. Padronizar renderizacao de imagens para usar `buildAssetUrl`.
2. Cobrir fluxos de kits/evento/template:
- `frontend/js/kits-evento.js`
- `frontend/js/kits-templates.js`
- `frontend/paginas/inscricao/modalidade.php`
3. Garantir compatibilidade com:
- caminho relativo salvo no banco
- caminho completo HTTP
- fallback de placeholder.

## Fase 4 - Observabilidade e bloqueio de regressao

1. Adicionar validacao de resposta JSON em pontos de cURL/fetch server-side.
2. Registrar metrica/log padrao:
- `url_base_resolvido`
- `url_requisitada`
- `http_code`
- `content_type`
3. Criar smoke tests de URL:
- API termos
- imagem de kit
- regulamento
- pagamento retorno/webhook (ja usa `URL_BASE` no config MP).

## 5) Criterios de aceite (DoD)

1. Nao existe mais chamada para `/paginas/api/` em logs.
2. `termos.php` recebe JSON valido de `get_termos.php` em producao.
3. Imagens de kit renderizam sem depender de heuristica por pagina.
4. `URL_BASE` controla o dominio/base de forma unica em backend + frontend.
5. Smoke de URLs passa em ambiente local e producao.

## 6) Execucao e seguranca de deploy

1. Deploy em 2 passos:
- Passo A: helper + hotfix termos + logs.
- Passo B: migracao dos consumidores restantes (frontend/backend).
2. Rollback:
- manter fallback para base dinamica enquanto `URL_BASE` nao estiver definido.
3. Variavel obrigatoria de producao:
- `URL_BASE=https://www.movamazon.com.br`

## 7) Registro da solucao aprovada

A solucao oficial para evitar recorrencia e:

1. Centralizar resolucao de base URL.
2. Eliminar montagem manual por tela/rota.
3. Tornar `URL_BASE` a fonte canonica.
4. Validar JSON e HTTP em todas as chamadas internas por URL.

