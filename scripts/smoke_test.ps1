param(
    [string]$BaseUrl = "http://localhost/movamazon",
    [string]$Email = "",
    [string]$Senha = "",
    [int]$TimeoutSec = 20,
    [switch]$SkipLogin
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

function Write-Fail {
    param([string]$Message)
    Write-Host "[FAIL] $Message" -ForegroundColor Red
}

function New-Result {
    param(
        [string]$Name,
        [string]$Status,
        [int]$HttpCode = 0,
        [double]$DurationMs = 0,
        [string]$Message = ""
    )
    return [ordered]@{
        name = $Name
        status = $Status
        http_code = $HttpCode
        duration_ms = [Math]::Round($DurationMs, 2)
        message = $Message
    }
}

function Invoke-JsonRequest {
    param(
        [string]$Method,
        [string]$Url,
        [Microsoft.PowerShell.Commands.WebRequestSession]$Session,
        [object]$Body = $null
    )

    $params = @{
        Method = $Method
        Uri = $Url
        WebSession = $Session
        TimeoutSec = $TimeoutSec
        UseBasicParsing = $true
        ErrorAction = "Stop"
    }

    if ($null -ne $Body) {
        $params.ContentType = "application/json; charset=utf-8"
        $params.Body = ($Body | ConvertTo-Json -Compress)
    }

    return Invoke-WebRequest @params
}

function Get-StatusCodeFromException {
    param($Exception)
    if ($Exception -and $Exception.Response) {
        try {
            return [int]$Exception.Response.StatusCode
        } catch {
            return 0
        }
    }
    return 0
}

if ([string]::IsNullOrWhiteSpace($Email)) {
    $Email = [Environment]::GetEnvironmentVariable("SMOKE_EMAIL")
}
if ([string]::IsNullOrWhiteSpace($Senha)) {
    $Senha = [Environment]::GetEnvironmentVariable("SMOKE_PASSWORD")
}

$logsDir = Join-Path (Get-Location) "logs"
if (-not (Test-Path $logsDir)) {
    New-Item -ItemType Directory -Path $logsDir | Out-Null
}

$stamp = Get-Date -Format "yyyyMMdd_HHmmss"
$reportFile = Join-Path $logsDir "smoke_test_$stamp.json"
$reportLast = Join-Path $logsDir "smoke_test_last.json"

$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$checks = New-Object System.Collections.Generic.List[object]
$overallStart = Get-Date

function Add-Check {
    param([object]$Result)
    $checks.Add($Result) | Out-Null
    if ($Result.status -eq "PASS") {
        Write-Ok ("{0} ({1})" -f $Result.name, $Result.http_code)
    } elseif ($Result.status -eq "SKIP") {
        Write-Info ("{0} (skip) - {1}" -f $Result.name, $Result.message)
    } else {
        Write-Fail ("{0} ({1}) - {2}" -f $Result.name, $Result.http_code, $Result.message)
    }
}

Write-Info ("BaseUrl: {0}" -f $BaseUrl)

# 1) Home publica
try {
    $start = Get-Date
    $resp = Invoke-WebRequest -Uri "$BaseUrl/frontend/paginas/public/index.php" -WebSession $session -TimeoutSec $TimeoutSec -UseBasicParsing -ErrorAction Stop
    $ok = ($resp.StatusCode -eq 200) -and ($resp.Content -match "<html" -or $resp.Content -match "<!DOCTYPE")
    $status = if ($ok) { "PASS" } else { "FAIL" }
    $msg = if ($ok) { "Pagina publica carregada" } else { "Resposta inesperada da home publica" }
    Add-Check (New-Result -Name "public_home" -Status $status -HttpCode ([int]$resp.StatusCode) -DurationMs ((Get-Date) - $start).TotalMilliseconds -Message $msg)
} catch {
    $code = Get-StatusCodeFromException $_.Exception
    Add-Check (New-Result -Name "public_home" -Status "FAIL" -HttpCode $code -DurationMs 0 -Message $_.Exception.Message)
}

# 2) API publica de estados
try {
    $start = Get-Date
    $resp = Invoke-JsonRequest -Method "GET" -Url "$BaseUrl/api/localidades/estados.php" -Session $session
    $json = $resp.Content | ConvertFrom-Json
    $ok = ($resp.StatusCode -eq 200) -and ($json.success -eq $true) -and ($json.estados.Count -gt 0)
    $status = if ($ok) { "PASS" } else { "FAIL" }
    $msg = if ($ok) { "API de estados OK" } else { "Formato inesperado em /api/localidades/estados.php" }
    Add-Check (New-Result -Name "api_estados_publico" -Status $status -HttpCode ([int]$resp.StatusCode) -DurationMs ((Get-Date) - $start).TotalMilliseconds -Message $msg)
} catch {
    $code = Get-StatusCodeFromException $_.Exception
    Add-Check (New-Result -Name "api_estados_publico" -Status "FAIL" -HttpCode $code -DurationMs 0 -Message $_.Exception.Message)
}

# 3) Dashboard sem login deve ser negado
try {
    $start = Get-Date
    $resp = Invoke-JsonRequest -Method "GET" -Url "$BaseUrl/api/participante/get_dashboard_data.php" -Session $session
    $json = $resp.Content | ConvertFrom-Json
    $ok = ($resp.StatusCode -eq 403) -or (($json.success -eq $false) -and ($json.message -match "Acesso negado"))
    $status = if ($ok) { "PASS" } else { "FAIL" }
    $msg = if ($ok) { "Acesso bloqueado sem sessao (esperado)" } else { "Endpoint respondeu como logado sem sessao" }
    Add-Check (New-Result -Name "dashboard_sem_login" -Status $status -HttpCode ([int]$resp.StatusCode) -DurationMs ((Get-Date) - $start).TotalMilliseconds -Message $msg)
} catch {
    $code = Get-StatusCodeFromException $_.Exception
    if ($code -eq 403) {
        Add-Check (New-Result -Name "dashboard_sem_login" -Status "PASS" -HttpCode 403 -DurationMs 0 -Message "Acesso bloqueado sem sessao (esperado)")
    } else {
        Add-Check (New-Result -Name "dashboard_sem_login" -Status "FAIL" -HttpCode $code -DurationMs 0 -Message $_.Exception.Message)
    }
}

$doLogin = (-not $SkipLogin) -and (-not [string]::IsNullOrWhiteSpace($Email)) -and (-not [string]::IsNullOrWhiteSpace($Senha))

if ($doLogin) {
    Write-Info "Executando bloco autenticado"

    # 4) Login API
    try {
        $start = Get-Date
        $resp = Invoke-JsonRequest -Method "POST" -Url "$BaseUrl/api/auth/login.php" -Session $session -Body @{ email = $Email; senha = $Senha }
        $json = $resp.Content | ConvertFrom-Json
        $ok = ($resp.StatusCode -eq 200) -and ($json.success -eq $true)
        $status = if ($ok) { "PASS" } else { "FAIL" }
        $msg = if ($ok) { "Login API OK" } else { "Falha de autenticacao (verifique email/senha)" }
        Add-Check (New-Result -Name "api_login" -Status $status -HttpCode ([int]$resp.StatusCode) -DurationMs ((Get-Date) - $start).TotalMilliseconds -Message $msg)
    } catch {
        $code = Get-StatusCodeFromException $_.Exception
        Add-Check (New-Result -Name "api_login" -Status "FAIL" -HttpCode $code -DurationMs 0 -Message $_.Exception.Message)
    }

    # 5) Sessao ativa
    try {
        $start = Get-Date
        $resp = Invoke-JsonRequest -Method "GET" -Url "$BaseUrl/api/auth/check_session.php" -Session $session
        $json = $resp.Content | ConvertFrom-Json
        $ok = ($resp.StatusCode -eq 200) -and ($json.success -eq $true) -and ($json.logged_in -eq $true)
        $status = if ($ok) { "PASS" } else { "FAIL" }
        $msg = if ($ok) { "Sessao autenticada confirmada" } else { "Sessao nao ficou autenticada apos login" }
        Add-Check (New-Result -Name "check_session_logado" -Status $status -HttpCode ([int]$resp.StatusCode) -DurationMs ((Get-Date) - $start).TotalMilliseconds -Message $msg)
    } catch {
        $code = Get-StatusCodeFromException $_.Exception
        Add-Check (New-Result -Name "check_session_logado" -Status "FAIL" -HttpCode $code -DurationMs 0 -Message $_.Exception.Message)
    }

    # 6) Dashboard logado
    try {
        $start = Get-Date
        $resp = Invoke-JsonRequest -Method "GET" -Url "$BaseUrl/api/participante/get_dashboard_data.php" -Session $session
        $json = $resp.Content | ConvertFrom-Json
        $ok = ($resp.StatusCode -eq 200) -and ($json.success -eq $true) -and ($null -ne $json.estatisticas)
        $status = if ($ok) { "PASS" } else { "FAIL" }
        $msg = if ($ok) { "Dashboard de participante carregado" } else { "Resposta inesperada do dashboard logado" }
        Add-Check (New-Result -Name "dashboard_logado" -Status $status -HttpCode ([int]$resp.StatusCode) -DurationMs ((Get-Date) - $start).TotalMilliseconds -Message $msg)
    } catch {
        $code = Get-StatusCodeFromException $_.Exception
        Add-Check (New-Result -Name "dashboard_logado" -Status "FAIL" -HttpCode $code -DurationMs 0 -Message $_.Exception.Message)
    }

    # 7) Minhas inscricoes
    try {
        $start = Get-Date
        $resp = Invoke-JsonRequest -Method "GET" -Url "$BaseUrl/api/participante/get_inscricoes.php" -Session $session
        $json = $resp.Content | ConvertFrom-Json
        $ok = ($resp.StatusCode -eq 200) -and ($json.success -eq $true) -and ($null -ne $json.inscricoes)
        $status = if ($ok) { "PASS" } else { "FAIL" }
        $msg = if ($ok) { "Inscricoes do participante carregadas" } else { "Resposta inesperada em get_inscricoes" }
        Add-Check (New-Result -Name "inscricoes_logado" -Status $status -HttpCode ([int]$resp.StatusCode) -DurationMs ((Get-Date) - $start).TotalMilliseconds -Message $msg)
    } catch {
        $code = Get-StatusCodeFromException $_.Exception
        Add-Check (New-Result -Name "inscricoes_logado" -Status "FAIL" -HttpCode $code -DurationMs 0 -Message $_.Exception.Message)
    }
} else {
    Add-Check (New-Result -Name "bloco_autenticado" -Status "SKIP" -HttpCode 0 -DurationMs 0 -Message "Informe -Email/-Senha ou variaveis SMOKE_EMAIL/SMOKE_PASSWORD")
}

$failed = @($checks | Where-Object { $_.status -eq "FAIL" }).Count
$passed = @($checks | Where-Object { $_.status -eq "PASS" }).Count
$skipped = @($checks | Where-Object { $_.status -eq "SKIP" }).Count
$durationTotal = ((Get-Date) - $overallStart).TotalSeconds

$summary = [ordered]@{
    generated_at = (Get-Date).ToString("yyyy-MM-ddTHH:mm:ssK")
    base_url = $BaseUrl
    totals = [ordered]@{
        passed = $passed
        failed = $failed
        skipped = $skipped
        total = $checks.Count
        duration_seconds = [Math]::Round($durationTotal, 2)
    }
    checks = $checks
    status = if ($failed -eq 0) { "PASS" } else { "FAIL" }
}

$json = $summary | ConvertTo-Json -Depth 8
[System.IO.File]::WriteAllText($reportFile, $json, (New-Object System.Text.UTF8Encoding($false)))
[System.IO.File]::WriteAllText($reportLast, $json, (New-Object System.Text.UTF8Encoding($false)))

Write-Host ""
Write-Host "========== RESULTADO SMOKE TEST ==========" -ForegroundColor Cyan
Write-Host ("PASS: {0} | FAIL: {1} | SKIP: {2}" -f $passed, $failed, $skipped)
Write-Host ("Relatorio: {0}" -f $reportFile)
if ($failed -eq 0) {
    Write-Host "STATUS: APROVADO" -ForegroundColor Green
    Write-Host "==========================================" -ForegroundColor Cyan
    exit 0
}

Write-Host "STATUS: REPROVADO" -ForegroundColor Red
Write-Host "==========================================" -ForegroundColor Cyan
exit 1
