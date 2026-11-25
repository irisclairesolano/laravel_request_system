param(
    [string]$ProjectName = "iris89",
    [string]$EnvFile = ".env.container",
    [string]$BaselinePath = "config-audit/baseline.example"
)

$ErrorActionPreference = "Stop"

if (-not (Test-Path $BaselinePath)) {
    Write-Error "Baseline file not found: $BaselinePath"
    exit 1
}

# Parse nonsecret baseline key=value pairs
$baseline = @{}
Get-Content $BaselinePath | ForEach-Object {
    $line = $_.Trim()
    if ($line -and -not $line.StartsWith("#") -and $line.Contains("=")) {
        $parts = $line.Split("=", 2)
        $baseline[$parts[0].Trim()] = $parts[1].Trim()
    }
}

$hasFailure = $false

function Write-CheckResult {
    param(
        [string]$Name,
        [ValidateSet("PASS", "FAIL", "UNVERIFIED")]
        [string]$Status
    )
    Write-Output "$Name $Status"
    if ($Status -eq "FAIL") {
        $script:hasFailure = $true
    }
}

# 1. Application-context checks (never print raw env vars, keys, or cached config)
$tinkerCmd = "echo (config('app.debug') === false ? 'debug_off PASS' : 'debug_off FAIL') . PHP_EOL . (!empty(config('app.key')) ? 'app_key_present PASS' : 'app_key_present FAIL') . PHP_EOL;"
$appChecks = & docker compose -p $ProjectName --env-file $EnvFile exec -T app php artisan tinker --execute="$tinkerCmd" 2>$null

if ($LASTEXITCODE -ne 0 -or -not $appChecks) {
    Write-CheckResult "debug_off" "FAIL"
    Write-CheckResult "app_key_present" "FAIL"
} else {
    foreach ($line in ($appChecks -split "`r?`n")) {
        $trimmed = $line.Trim()
        if ($trimmed -match '^(debug_off|app_key_present)\s+(PASS|FAIL)$') {
            Write-CheckResult $Matches[1] $Matches[2]
        }
    }
}

# 2. Container identity and writable paths
$expectedUser = $baseline["SERVICE_IDENTITY"]
$fsCmd = "id -un; test -w storage && echo storage_writable_PASS || echo storage_writable_FAIL; test -w bootstrap/cache && echo bootstrap_cache_writable_PASS || echo bootstrap_cache_writable_FAIL"
$fsOut = & docker compose -p $ProjectName --env-file $EnvFile exec -T app sh -lc "$fsCmd" 2>$null

if ($LASTEXITCODE -ne 0 -or -not $fsOut) {
    Write-CheckResult "service_identity_non_root" "FAIL"
    Write-CheckResult "storage_writable" "FAIL"
    Write-CheckResult "bootstrap_cache_writable" "FAIL"
} else {
    $lines = $fsOut -split "`r?`n" | ForEach-Object { $_.Trim() } | Where-Object { $_ }
    $actualUser = if ($lines.Count -ge 1) { $lines[0] } else { "" }
    Write-CheckResult "service_identity_non_root" $(if ($actualUser -eq $expectedUser) { "PASS" } else { "FAIL" })
    Write-CheckResult "storage_writable" $(if ($lines -contains "storage_writable_PASS") { "PASS" } else { "FAIL" })
    Write-CheckResult "bootstrap_cache_writable" $(if ($lines -contains "bootstrap_cache_writable_PASS") { "PASS" } else { "FAIL" })
}

# 3. Repository-derived document root & local port binding
$expectedRoot = $baseline["DOCUMENT_ROOT"]
$nginxMatch = Select-String -Path "docker/nginx/default.conf" -Pattern "root\s+$([regex]::Escape($expectedRoot));" -Quiet
Write-CheckResult "document_root_configured" $(if ($nginxMatch) { "PASS" } else { "FAIL" })

$expectedBind = $baseline["EXPOSED_WEB_BIND"]
$portMatch = Select-String -Path "compose.yaml" -Pattern ([regex]::Escape($expectedBind)) -Quiet
Write-CheckResult "local_port_bound" $(if ($portMatch) { "PASS" } else { "FAIL" })

# 4. Controls unavailable in local-only environment
Write-CheckResult "database_access_scope" "UNVERIFIED"
Write-CheckResult "public_tls" "UNVERIFIED"
Write-CheckResult "hosting_firewall" "UNVERIFIED"
Write-CheckResult "hosted_patch_responsibility" "UNVERIFIED"

if ($hasFailure) {
    exit 1
}
exit 0
