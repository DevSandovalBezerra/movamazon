# Guia de Deploy Resumido (Ultimas Alteracoes)

Data: 2026-02-24  
Escopo: UTF-8, correcao de 500 no dashboard, smoke test automatico e CI

## 1) O que entrou nesta entrega

1. Correcao de mojibake na origem (frontend e pontos de API relacionados).
2. Auditoria UTF-8 com gate automatizado.
3. Remocao de fallback runtime de recodificacao (`corrigirMojibake`/`TextDecoder`).
4. Correcao de compatibilidade Apache 2.4+ em `.htaccess`:
- `api/.htaccess`
- `frontend/paginas/panel/.htaccess`
5. Smoke test automatico:
- `scripts/smoke_test.ps1`
- `npm run smoke:test`
- Integrado ao workflow `.github/workflows/phpunit.yml`.

## 2) Pre-deploy local (obrigatorio)

1. Rodar auditoria UTF-8:
```powershell
powershell -ExecutionPolicy Bypass -File scripts/utf8_audit.ps1
```

2. Confirmar resultado esperado no resumo:
- `json_headers_without_charset=0`
- `html_headers_without_charset=0`
- `files_with_mojibake_markers=0`
- `invalid_utf8_files=0`
- `runtime_fallback_markers=0`

3. Rodar smoke test local:
```powershell
npm run smoke:test
```

## 3) CI/CD (push e PR)

O workflow `phpunit.yml` agora executa, em ordem:
1. Unit tests
2. Integration tests
3. UTF-8 audit gate
4. Smoke test automatico (mesmo pipeline de pre-deploy)
5. Coverage e upload de artefatos

## 4) Configuracao recomendada no GitHub

Para habilitar o bloco autenticado do smoke test no CI, configurar secrets:
- `SMOKE_EMAIL`
- `SMOKE_PASSWORD`

Sem esses secrets, o smoke test roda checks publicos e marca bloco autenticado como `SKIP`.

## 5) Deploy resumido em producao

1. Garantir pipeline verde no commit da release.
2. Publicar release em canario (parcial de trafego).
3. Validar rapidamente:
- login participante
- dashboard participante
- tela de pagamento
- uma tela admin/organizador
4. Se tudo ok, ampliar para 100%.

## 6) Pos-deploy (primeiros 30-60 min)

1. Monitorar logs de aplicacao e Apache.
2. Confirmar ausencia de:
- `Invalid command 'Order'`
- erros 500 no dashboard
- regressao de caracteres corrompidos
3. Executar smoke test manual rapido no ambiente publicado.

## 7) Rollback rapido

Acionar rollback se houver regressao critica em fluxo de negocio:
1. Voltar pacote/codigo para release anterior estavel.
2. Limpar cache de aplicacao/CDN (se aplicavel).
3. Revalidar login + dashboard + pagamento.
