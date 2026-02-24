# Smoke Test Automatico

Script: `scripts/smoke_test.ps1`

## 1) Execucao rapida (sem login)

Valida endpoints publicos e comportamento esperado sem sessao:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/smoke_test.ps1
```

Ou:

```powershell
npm run smoke:test
```

## 2) Execucao com login (participante)

### Opcao A: parametros

```powershell
powershell -ExecutionPolicy Bypass -File scripts/smoke_test.ps1 -Email "seu_email" -Senha "sua_senha"
```

### Opcao B: variaveis de ambiente

```powershell
$env:SMOKE_EMAIL="seu_email"
$env:SMOKE_PASSWORD="sua_senha"
powershell -ExecutionPolicy Bypass -File scripts/smoke_test.ps1
```

## 3) O que o script valida

1. Home publica
2. API publica de estados
3. Dashboard sem login (deve bloquear acesso)
4. Login API (quando credenciais forem informadas)
5. Sessao autenticada
6. Dashboard logado
7. Inscricoes do participante

## 4) Resultado e relatorios

- O script retorna:
  - `0` quando aprovado
  - `1` quando reprovado
- Relatorios JSON:
  - `logs/smoke_test_YYYYMMDD_HHMMSS.json`
  - `logs/smoke_test_last.json`

## 5) CI (GitHub Actions)

O workflow [`phpunit.yml`](/c:/wamp64/www/movamazon/.github/workflows/phpunit.yml) executa o smoke test no mesmo pipeline de pre-deploy.

- Base URL usada no CI: `http://127.0.0.1:8000` (servidor PHP temporario)
- Credenciais opcionais via secrets:
  - `SMOKE_EMAIL`
  - `SMOKE_PASSWORD`

Se os secrets nao forem configurados, o bloco autenticado fica como `SKIP` e o smoke test continua valido para os checks publicos.
