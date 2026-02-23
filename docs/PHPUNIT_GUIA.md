# Guia PHPUnit (MovAmazon)

## Requisitos

- PHP 8.1+
- Composer
- Extensoes: `dom`, `xml`, `xmlwriter`, `mbstring`, `pdo_mysql`

## Comandos principais

- Unit:
  - `composer test`
  - `composer test:unit`
- Integration:
  - PowerShell:
    - `$env:RUN_INTEGRATION_TESTS='1'; composer test:integration`
- Tudo:
  - `composer test:all`
- Cobertura:
  - `composer test:coverage`

## Gate manual pre-deploy

Use sempre antes de deploy manual:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\pre_deploy_check.ps1 -PhpExe "C:\wamp64\bin\php\php8.3.x\php.exe"
```

Regra:

- `APROVADO`: deploy manual liberado.
- `REPROVADO`: deploy manual bloqueado.

## Ambiente de integracao

- A suite de integracao exige `RUN_INTEGRATION_TESTS=1`.
- O bootstrap de integracao exige DB terminando com `_test`.
- Sem `.env.testing`, o bootstrap tenta derivar de `.env` e forca sufixo `_test`.

Arquivo de referencia:

- `.env.testing.example`

## Estrutura de testes

- `tests/Unit`:
  - regras puras de financeiro, inscricao e mercadolivre
- `tests/Integration`:
  - testes com PDO real e transacao por teste (rollback no tearDown)

## CI

Workflow:

- `.github/workflows/phpunit.yml`

Pipeline executa:

1. `composer test`
2. `composer test:integration` com `RUN_INTEGRATION_TESTS=1`
3. cobertura com Xdebug (`clover.xml` como artifact)

Deploy deve ser bloqueado se qualquer etapa falhar.
