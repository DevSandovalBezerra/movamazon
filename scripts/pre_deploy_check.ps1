param(
    [Parameter(Mandatory = $true)]
    [string]$PhpExe,
    [switch]$SkipInstall,
    [switch]$SkipCoverage
)

$ErrorActionPreference = "Stop"

function Write-Info {
    param([string]$Message)
    Write-Host "[INFO] $Message"
}

function Write-Ok {
    param([string]$Message)
    Write-Host "[OK] $Message" -ForegroundColor Green
}

function Write-Warn {
    param([string]$Message)
    Write-Host "[WARN] $Message" -ForegroundColor Yellow
}

function Write-Fail {
    param([string]$Message)
    Write-Host "[FAIL] $Message" -ForegroundColor Red
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

function Ensure-TestingEnv {
    if (Test-Path ".env.testing") {
        return
    }

    Ensure-File -Path ".env.testing.example" -Message "Arquivo .env.testing.example nao encontrado."
    Ensure-File -Path ".env" -Message "Arquivo .env nao encontrado."

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
    Write-Info ".env.testing criado automaticamente com DB_NAME=$testDbName"
}

function Invoke-Step {
    param(
        [string]$Name,
        [scriptblock]$Script
    )

    Write-Info "Executando: $Name"
    & $Script
    if ($LASTEXITCODE -ne 0) {
        throw "$Name falhou com codigo $LASTEXITCODE"
    }
    Write-Ok "$Name concluido"
}

function Get-CoveragePercent {
    param([string]$CloverPath)
    if (-not (Test-Path $CloverPath)) {
        return $null
    }

    try {
        [xml]$xml = Get-Content -Path $CloverPath
        $metrics = $xml.coverage.project.metrics
        if ($null -eq $metrics) {
            return $null
        }

        $covered = [double]$metrics.coveredstatements
        $total = [double]$metrics.statements
        if ($total -le 0) {
            return 0.0
        }

        return [Math]::Round(($covered / $total) * 100, 2)
    } catch {
        return $null
    }
}

$startAt = Get-Date
$logsDir = Join-Path -Path (Get-Location) -ChildPath "logs"
if (-not (Test-Path $logsDir)) {
    New-Item -ItemType Directory -Path $logsDir | Out-Null
}
$stamp = Get-Date -Format "yyyyMMdd_HHmmss"
$logFile = Join-Path $logsDir "pre_deploy_check_$stamp.log"
$summaryFile = Join-Path $logsDir "pre_deploy_check_$stamp.json"
$lastSummaryFile = Join-Path $logsDir "pre_deploy_check_last.json"

$results = [ordered]@{
    timestamp = (Get-Date).ToString("s")
    php = ""
    install = "SKIPPED"
    unit = "NOT_RUN"
    integration = "NOT_RUN"
    coverage = "NOT_RUN"
    coverage_percent = $null
    overall = "REPROVADO"
    duration_seconds = 0
}

try {
    Start-Transcript -Path $logFile -Force | Out-Null

    Ensure-File -Path $PhpExe -Message "PHP nao encontrado em: $PhpExe"
    Ensure-File -Path "composer.json" -Message "Execute na raiz do projeto (composer.json nao encontrado)."
    Ensure-TestingEnv

    $phpDir = Split-Path -Path $PhpExe -Parent
    $env:PATH = "$phpDir;$env:PATH"
    $phpVersion = & $PhpExe -v | Select-Object -First 1
    $results.php = $phpVersion
    Write-Info "PHP ativo: $phpVersion"

    if ($phpVersion -notmatch "PHP 8\.3") {
        Write-Warn "PHP nao parece 8.3.x. Recomendado para este projeto."
    }

    $composerCmd = Get-Command composer -ErrorAction SilentlyContinue
    if (-not $composerCmd) {
        throw "Composer nao encontrado no PATH."
    }

    if (-not $SkipInstall) {
        Invoke-Step -Name "composer install" -Script { composer install --no-interaction --prefer-dist }
        $results.install = "OK"
    } else {
        Write-Warn "Instalacao pulada por parametro -SkipInstall"
    }

    Invoke-Step -Name "composer test (unit)" -Script { composer test }
    $results.unit = "OK"

    $env:RUN_INTEGRATION_TESTS = "1"
    try {
        Invoke-Step -Name "composer test:integration" -Script { composer test:integration }
        $results.integration = "OK"
    } finally {
        Remove-Item Env:RUN_INTEGRATION_TESTS -ErrorAction SilentlyContinue
    }

    if (-not $SkipCoverage) {
        $hasXdebug = ((& $PhpExe -m) -match "^xdebug$") -ne $null
        if (-not $hasXdebug) {
            throw "Cobertura exigida, mas Xdebug nao esta ativo no PHP CLI."
        }

        Invoke-Step -Name "coverage (phpunit unit + clover)" -Script {
            & $PhpExe -d xdebug.mode=coverage vendor/bin/phpunit --testsuite Unit --coverage-text --coverage-clover build/logs/clover.xml
        }

        $results.coverage = "OK"
        $results.coverage_percent = Get-CoveragePercent -CloverPath "build/logs/clover.xml"
    } else {
        Write-Warn "Cobertura pulada por parametro -SkipCoverage"
        $results.coverage = "SKIPPED"
    }

    $results.overall = "APROVADO"
    Write-Host ""
    Write-Host "========== RESULTADO PRE-DEPLOY ==========" -ForegroundColor Cyan
    Write-Host "APROVADO" -ForegroundColor Green
    Write-Host "Log: $logFile"
    if ($results.coverage_percent -ne $null) {
        Write-Host "Cobertura (linhas): $($results.coverage_percent)%"
    }
    Write-Host "=========================================="
    exit 0
} catch {
    $errorMessage = $_.Exception.Message
    Write-Fail $errorMessage

    Write-Host ""
    Write-Host "========== RESULTADO PRE-DEPLOY ==========" -ForegroundColor Cyan
    Write-Host "REPROVADO" -ForegroundColor Red
    Write-Host "Motivo: $errorMessage"
    Write-Host "Log: $logFile"
    Write-Host "=========================================="
    exit 1
} finally {
    $elapsed = (Get-Date) - $startAt
    $results.duration_seconds = [Math]::Round($elapsed.TotalSeconds, 2)

    $results | ConvertTo-Json -Depth 6 | Set-Content -Path $summaryFile -Encoding ASCII
    $results | ConvertTo-Json -Depth 6 | Set-Content -Path $lastSummaryFile -Encoding ASCII

    try {
        Stop-Transcript | Out-Null
    } catch {
    }
}
