param(
    [Parameter(Mandatory = $true)]
    [string]$PhpExe,
    [switch]$SkipCoverage
)

$ErrorActionPreference = "Stop"

function Write-Info {
    param([string]$Message)
    Write-Host "[INFO] $Message"
}

function Write-Warn {
    param([string]$Message)
    Write-Host "[WARN] $Message" -ForegroundColor Yellow
}

function Ensure-File {
    param([string]$Path, [string]$Message)
    if (-not (Test-Path $Path)) {
        throw $Message
    }
}

function Get-EnvValue {
    param([string]$FilePath, [string]$Key)
    if (-not (Test-Path $FilePath)) {
        return ""
    }

    $line = Get-Content $FilePath | Where-Object { $_ -match "^\s*$Key\s*=" } | Select-Object -First 1
    if (-not $line) {
        return ""
    }

    $value = $line -replace "^\s*$Key\s*=\s*", ""
    return $value.Trim().Trim("'`"")
}

Write-Info "Validando pre-condicoes"

Ensure-File -Path $PhpExe -Message "PHP nao encontrado em: $PhpExe"
Ensure-File -Path "composer.json" -Message "Execute este script na raiz do projeto (composer.json nao encontrado)."
Ensure-File -Path ".env" -Message "Arquivo .env nao encontrado na raiz."
Ensure-File -Path ".env.testing.example" -Message "Arquivo .env.testing.example nao encontrado na raiz."

$phpDir = Split-Path -Path $PhpExe -Parent
$env:PATH = "$phpDir;$env:PATH"

$phpVersion = & $PhpExe -v | Select-Object -First 1
Write-Info "PHP ativo: $phpVersion"

if ($phpVersion -notmatch "PHP 8\.3") {
    Write-Warn "PHP atual nao parece 8.3.x. Recomenda-se usar PHP 8.3 no Wamp."
}

Write-Info "Gerando .env.testing se necessario"
if (-not (Test-Path ".env.testing")) {
    Copy-Item ".env.testing.example" ".env.testing"

    $dbHost = Get-EnvValue -FilePath ".env" -Key "DB_HOST"
    $dbPort = Get-EnvValue -FilePath ".env" -Key "DB_PORT"
    $dbName = Get-EnvValue -FilePath ".env" -Key "DB_NAME"
    $dbUser = Get-EnvValue -FilePath ".env" -Key "DB_USER"
    $dbPass = Get-EnvValue -FilePath ".env" -Key "DB_PASS"

    if ($dbHost -eq "") { $dbHost = "127.0.0.1" }
    if ($dbPort -eq "") { $dbPort = "3306" }
    if ($dbName -eq "") { $dbName = "movamazon" }
    if ($dbUser -eq "") { $dbUser = "root" }

    $testDbName = if ($dbName.ToLower().EndsWith("_test")) { $dbName } else { "${dbName}_test" }

    $content = @(
        "DB_HOST=$dbHost"
        "DB_PORT=$dbPort"
        "DB_NAME=$testDbName"
        "DB_USER=$dbUser"
        "DB_PASS=$dbPass"
    )

    Set-Content -Path ".env.testing" -Value $content -Encoding ASCII
    Write-Info ".env.testing criado com DB_NAME=$testDbName"
} else {
    Write-Info ".env.testing ja existe, mantendo arquivo atual"
}

Write-Info "Verificando Composer"
$composerCmd = Get-Command composer -ErrorAction SilentlyContinue
if (-not $composerCmd) {
    throw "Composer nao encontrado no PATH. Instale o Composer no Windows."
}

Write-Info "Instalando dependencias"
& composer install --no-interaction --prefer-dist
if ($LASTEXITCODE -ne 0) {
    throw "composer install falhou."
}

Write-Info "Executando testes unitarios"
& composer test
if ($LASTEXITCODE -ne 0) {
    throw "composer test falhou."
}

Write-Info "Executando testes de integracao"
$env:RUN_INTEGRATION_TESTS = "1"
& composer test:integration
$integrationExit = $LASTEXITCODE
Remove-Item Env:RUN_INTEGRATION_TESTS -ErrorAction SilentlyContinue
if ($integrationExit -ne 0) {
    throw "composer test:integration falhou."
}

if (-not $SkipCoverage) {
    Write-Info "Verificando Xdebug para cobertura"
    $modules = & $PhpExe -m
    $hasXdebug = $modules -match "^xdebug$"

    if ($hasXdebug) {
        Write-Info "Xdebug detectado, executando cobertura"
        & $PhpExe -d xdebug.mode=coverage vendor/bin/phpunit --testsuite Unit --coverage-text --coverage-clover build/logs/clover.xml
        if ($LASTEXITCODE -ne 0) {
            throw "Cobertura falhou."
        }
    } else {
        Write-Warn "Xdebug nao detectado. Cobertura foi ignorada. Habilite xdebug para usar cobertura."
    }
}

Write-Info "Setup concluido com sucesso."
Write-Info "Para deploy manual, execute o gate: powershell -NoProfile -ExecutionPolicy Bypass -File .\\scripts\\pre_deploy_check.ps1 -PhpExe `"$PhpExe`""
