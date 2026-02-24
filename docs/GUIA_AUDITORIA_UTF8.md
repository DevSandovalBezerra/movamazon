# Guia de Auditoria UTF-8 (Operacional)

Data: 2026-02-24
Ambiente: PowerShell (Windows)

## 0) Auditoria automatica (recomendado)

```powershell
powershell -ExecutionPolicy Bypass -File scripts\utf8_audit.ps1
```

Saidas:
- `build/utf8_audit_report.json`
- `docs/RELATORIO_AUDITORIA_UTF8.md`

## 1) Contar endpoints JSON sem charset

```powershell
rg -n "header\('Content-Type: application/json" api | rg -v "charset=utf-8|charset=UTF-8" | Measure-Object -Line
```

Esperado no fechamento: `Lines = 0`

## 2) Contar arquivos com marcadores de mojibake

```powershell
powershell -ExecutionPolicy Bypass -File scripts\utf8_audit.ps1
$report = Get-Content build\utf8_audit_report.json -Raw | ConvertFrom-Json
$report.files_with_mojibake_markers
```

Esperado no fechamento: `Lines = 0` (escopo de producao)

## 2.1) Contar arquivos invalidos em UTF-8

```powershell
$report = Get-Content build\utf8_audit_report.json -Raw | ConvertFrom-Json
$report.invalid_utf8_files
```

Esperado no fechamento: `0`

## 3) Localizar fallback runtime de correcao

```powershell
rg -n "corrigirMojibake\(|TextDecoder\('utf-8'\)|countMojibakeMarkers" frontend\js
```

Esperado no fechamento: removido apos janela de estabilizacao.

## 4) Verificar resposta HTTP com charset

```powershell
$r = Invoke-WebRequest -Uri "http://localhost/movamazon/api/participante/get_dashboard_data.php" -Method GET -UseBasicParsing
$r.Headers["Content-Type"]
```

Esperado: `application/json; charset=utf-8` (ou `UTF-8`)

## 5) Checklist minimo por release

1. Rodar comandos 1-3 e anexar resultado no PR.
2. Validar ao menos 1 endpoint de cada modulo principal com comando 4.
3. Executar smoke funcional de inscricao/pagamento/dashboard.
4. Registrar diff "antes/depois" no changelog da release.
