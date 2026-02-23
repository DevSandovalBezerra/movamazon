@echo off
setlocal

set "DEFAULT_PHP=C:\wamp64\bin\php\php8.3.28\php.exe"
set "PHP_EXE=%~1"

if "%PHP_EXE%"=="" set "PHP_EXE=%DEFAULT_PHP%"

if not exist "%PHP_EXE%" (
    echo [ERRO] PHP nao encontrado em: "%PHP_EXE%"
    echo Uso: run_pre_deploy_check.bat "C:\caminho\php.exe"
    exit /b 1
)

if not exist ".\scripts\pre_deploy_check.ps1" (
    echo [ERRO] Script nao encontrado: .\scripts\pre_deploy_check.ps1
    exit /b 1
)

echo [INFO] Executando pre-deploy check com: "%PHP_EXE%"
powershell -NoProfile -ExecutionPolicy Bypass -File ".\scripts\pre_deploy_check.ps1" -PhpExe "%PHP_EXE%"
set "EXIT_CODE=%ERRORLEVEL%"

if "%EXIT_CODE%"=="0" (
    echo [OK] Pre-deploy APROVADO.
) else (
    echo [FAIL] Pre-deploy REPROVADO.
)

exit /b %EXIT_CODE%
