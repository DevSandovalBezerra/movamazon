# Guia: Migracao para Wamp + PHPUnit

## Objetivo

Rodar o projeto no Wamp com PHP 8.3 e testes automatizados (unit, integration e cobertura).

## 1. Preparar Wamp

1. Instale o WampServer com MySQL 8 e PHP 8.3.x.
2. Copie o projeto para `C:\wamp64\www\movamazon`.
3. No menu do Wamp, selecione PHP 8.3 como versao ativa.
4. Reinicie os servicos do Wamp.

## 2. Habilitar extensoes PHP no Wamp

No menu do Wamp (icone da bandeja):

1. `PHP` -> `PHP Extensions`:
2. Habilite:
   - `curl`
   - `mbstring`
   - `mysqli`
   - `pdo_mysql`
   - `xml`
   - `xmlreader`
   - `xmlwriter`
   - `dom`
   - `zip`
3. Reinicie os servicos.

## 3. Habilitar Xdebug para cobertura

No menu do Wamp:

1. Ative Xdebug (`php_xdebug`).
2. Abra `php.ini` da versao ativa (CLI).
3. Garanta as chaves:
   - `xdebug.mode=coverage,develop`
   - `xdebug.start_with_request=no`
4. Reinicie os servicos.

Validacao:

```powershell
C:\wamp64\bin\php\php8.3.x\php.exe -m | findstr /I xdebug
```

## 4. Executar setup automatizado

Na raiz do projeto, rode:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\setup_wamp_phpunit.ps1 -PhpExe "C:\wamp64\bin\php\php8.3.x\php.exe"
```

O script faz:

1. valida PHP/Composer;
2. cria `.env.testing` (se faltar), com DB terminando em `_test`;
3. roda `composer install`;
4. roda `composer test`;
5. roda `composer test:integration` com `RUN_INTEGRATION_TESTS=1`;
6. roda cobertura se Xdebug estiver ativo.

## 5. Gate manual de pre-deploy (obrigatorio)

Antes de qualquer deploy manual, rode:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\pre_deploy_check.ps1 -PhpExe "C:\wamp64\bin\php\php8.3.x\php.exe"
```

Saida esperada:

- `APROVADO` -> deploy pode prosseguir.
- `REPROVADO` -> deploy bloqueado ate corrigir.

Artefatos gerados:

- `logs/pre_deploy_check_YYYYMMDD_HHMMSS.log`
- `logs/pre_deploy_check_last.json`

## 6. Execucao manual (se preferir)

```powershell
composer install
composer test
$env:RUN_INTEGRATION_TESTS='1'; composer test:integration; Remove-Item Env:RUN_INTEGRATION_TESTS
php -d xdebug.mode=coverage vendor/bin/phpunit --testsuite Unit --coverage-text --coverage-clover build/logs/clover.xml
```

## 7. Pontos de seguranca

1. Nao rode integration contra DB de producao.
2. Use sempre DB `*_test` para integration.
3. Sem `APROVADO` no pre-deploy check, nao publicar manualmente.
4. CI em `.github/workflows/phpunit.yml` ja bloqueia pipeline com falha de testes.
