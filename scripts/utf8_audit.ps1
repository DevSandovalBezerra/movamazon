param(
    [string]$Root = ".",
    [string]$OutJson = "build/utf8_audit_report.json",
    [string]$OutMd = "docs/RELATORIO_AUDITORIA_UTF8.md"
)

$ErrorActionPreference = "Stop"

function Get-RepoPath([string]$p) {
    return (Resolve-Path (Join-Path $Root $p)).Path
}

function Get-RelativePath([string]$base, [string]$path) {
    $baseUri = New-Object System.Uri(($base.TrimEnd('\') + '\'))
    $pathUri = New-Object System.Uri($path)
    return [System.Uri]::UnescapeDataString($baseUri.MakeRelativeUri($pathUri).ToString()).Replace('/', '\')
}

$utf8Strict = New-Object System.Text.UTF8Encoding($false, $true)
function Read-Utf8Text([string]$path) {
    return [System.IO.File]::ReadAllText($path, $utf8Strict)
}

$repoRoot = (Resolve-Path $Root).Path
$apiPath = Get-RepoPath "api"
$frontendPath = Get-RepoPath "frontend"

$jsonNoCharset = New-Object System.Collections.Generic.List[string]
$htmlNoCharset = New-Object System.Collections.Generic.List[string]
$invalidUtf8Files = New-Object System.Collections.Generic.List[string]

Get-ChildItem -Path $apiPath, $frontendPath -Recurse -File -Include *.php | ForEach-Object {
    $path = $_.FullName
    $rel = Get-RelativePath $repoRoot $path
    $content = $null
    try {
        $content = Read-Utf8Text $path
    } catch {
        $invalidUtf8Files.Add($rel)
        return
    }

    $lines = $content -split "`r`n|`n|`r"
    for ($idx = 0; $idx -lt $lines.Length; $idx++) {
        $lineNo = $idx + 1
        $line = $lines[$idx]

        if ($line -match 'header\s*\(\s*["'']Content-Type:\s*application/json' -and $line -notmatch 'charset\s*=\s*(utf-8|UTF-8)') {
            $jsonNoCharset.Add(("{0}:{1}" -f $rel, $lineNo))
        }

        if ($line -match 'header\s*\(\s*["'']Content-Type:\s*text/html' -and $line -notmatch 'charset\s*=\s*(utf-8|UTF-8)') {
            $htmlNoCharset.Add(("{0}:{1}" -f $rel, $lineNo))
        }
    }
}

$markerRegex = '(?:\u00C3[\u0080-\u00BF]|\u00C2[\u0080-\u00BF]|\u00E2[\u0080-\u00BF]{1,2}|\uFFFD|prefer\u00AAncia|Voc\u00AA|voc\u00AA|v\u00AAm|n\u00BAmero|m\u00BAltiplas|\u00BAteis|banc\u00E1rio\u00E1rio)'
$markerFiles = @()
$scanRoots = @("api", "frontend", "src")
$textExtensions = @(
    ".php", ".js", ".ts", ".tsx", ".jsx", ".css", ".scss", ".html", ".htm",
    ".json", ".txt", ".yml", ".yaml", ".xml", ".sql", ".ps1", ".sh",
    ".ini", ".conf", ".csv"
)
$textNames = @(".htaccess", ".env", ".env.example", ".gitattributes", ".editorconfig")

foreach ($scan in $scanRoots) {
    $scanPath = Join-Path $repoRoot $scan
    if (-not (Test-Path $scanPath)) { continue }

    Get-ChildItem -Path $scanPath -Recurse -File | Where-Object {
        $_.FullName -notmatch '\\vendor\\' -and
        $_.FullName -notmatch '\\node_modules\\' -and
        $_.FullName -notmatch '\\build\\' -and
        $_.FullName -notmatch '\\inscricao_EXEMPLO\\' -and
        $_.Extension -ne '.min.js'
    } | ForEach-Object {
        $ext = $_.Extension.ToLowerInvariant()
        $name = $_.Name.ToLowerInvariant()
        if (-not (($textExtensions -contains $ext) -or ($textNames -contains $name))) {
            return
        }

        $rel = Get-RelativePath $repoRoot $_.FullName
        $raw = $null
        try {
            $raw = Read-Utf8Text $_.FullName
        } catch {
            $invalidUtf8Files.Add($rel)
            return
        }

        if ($raw -match $markerRegex) {
            $markerFiles += $rel
        }
    }
}

$markerFiles = $markerFiles | Sort-Object -Unique
$invalidUtf8Files = $invalidUtf8Files | Sort-Object -Unique

$runtimeFallbackCount = 0
$fallbackPatterns = @(
    "corrigirMojibake(",
    "TextDecoder(",
    "countMojibakeMarkers"
)

$utilsPath = Join-Path $repoRoot "frontend\js"
if (Test-Path $utilsPath) {
    Get-ChildItem -Path $utilsPath -Recurse -File -Include *.js | ForEach-Object {
        $raw = $null
        try {
            $raw = Read-Utf8Text $_.FullName
        } catch {
            return
        }
        foreach ($pattern in $fallbackPatterns) {
            $runtimeFallbackCount += ([regex]::Matches($raw, [regex]::Escape($pattern))).Count
        }
    }
}

$report = [ordered]@{
    generated_at = (Get-Date).ToString("yyyy-MM-ddTHH:mm:ssK")
    json_headers_without_charset = $jsonNoCharset.Count
    html_headers_without_charset = $htmlNoCharset.Count
    files_with_mojibake_markers = $markerFiles.Count
    invalid_utf8_files = $invalidUtf8Files.Count
    runtime_fallback_markers = $runtimeFallbackCount
    samples = [ordered]@{
        json_headers_without_charset = @($jsonNoCharset | Select-Object -First 50)
        html_headers_without_charset = @($htmlNoCharset | Select-Object -First 50)
        files_with_mojibake_markers = @($markerFiles | Select-Object -First 120)
        invalid_utf8_files = @($invalidUtf8Files | Select-Object -First 120)
    }
}

$jsonText = $report | ConvertTo-Json -Depth 6
$outJsonPath = Join-Path $repoRoot $OutJson
$outJsonDir = Split-Path $outJsonPath -Parent
if (-not (Test-Path $outJsonDir)) { New-Item -Path $outJsonDir -ItemType Directory | Out-Null }
[System.IO.File]::WriteAllText($outJsonPath, $jsonText, (New-Object System.Text.UTF8Encoding($false)))

$lines = @()
$lines += "# Relatorio de Auditoria UTF-8"
$lines += ""
$lines += ("Gerado em: {0}" -f $report.generated_at)
$lines += ""
$lines += "## Resumo"
$lines += ""
$lines += ("- json_headers_without_charset: {0}" -f $report.json_headers_without_charset)
$lines += ("- html_headers_without_charset: {0}" -f $report.html_headers_without_charset)
$lines += ("- files_with_mojibake_markers: {0}" -f $report.files_with_mojibake_markers)
$lines += ("- invalid_utf8_files: {0}" -f $report.invalid_utf8_files)
$lines += ("- runtime_fallback_markers: {0}" -f $report.runtime_fallback_markers)
$lines += ""
$lines += "## Amostra de Arquivos Com Marcadores Mojibake"
$lines += ""
foreach ($entry in $report.samples.files_with_mojibake_markers) {
    $lines += ("- {0}" -f $entry)
}
$lines += ""
$lines += "## Amostra de Arquivos Invalidos em UTF-8"
$lines += ""
foreach ($entry in $report.samples.invalid_utf8_files) {
    $lines += ("- {0}" -f $entry)
}

$outMdPath = Join-Path $repoRoot $OutMd
$outMdDir = Split-Path $outMdPath -Parent
if (-not (Test-Path $outMdDir)) { New-Item -Path $outMdDir -ItemType Directory | Out-Null }
[System.IO.File]::WriteAllLines($outMdPath, $lines, (New-Object System.Text.UTF8Encoding($false)))

Write-Output ("json_headers_without_charset={0}" -f $report.json_headers_without_charset)
Write-Output ("html_headers_without_charset={0}" -f $report.html_headers_without_charset)
Write-Output ("files_with_mojibake_markers={0}" -f $report.files_with_mojibake_markers)
Write-Output ("invalid_utf8_files={0}" -f $report.invalid_utf8_files)
Write-Output ("runtime_fallback_markers={0}" -f $report.runtime_fallback_markers)
Write-Output ("json_report={0}" -f $OutJson)
Write-Output ("markdown_report={0}" -f $OutMd)
