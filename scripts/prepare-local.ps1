param(
    [int] $Port = 8000
)

$ErrorActionPreference = 'Stop'

$projectRoot = Resolve-Path (Join-Path $PSScriptRoot '..')
Set-Location $projectRoot

$localUrl = "http://127.0.0.1:$Port"
$envPath = Join-Path $projectRoot '.env'
$envExamplePath = Join-Path $projectRoot '.env.example'

if (-not (Test-Path $envPath) -and (Test-Path $envExamplePath)) {
    Copy-Item -LiteralPath $envExamplePath -Destination $envPath
}

function Set-DotEnvValue {
    param(
        [string] $Path,
        [string] $Key,
        [string] $Value
    )

    if (-not (Test-Path $Path)) {
        return
    }

    $lines = Get-Content -LiteralPath $Path -Encoding UTF8
    $pattern = "^\s*$([regex]::Escape($Key))="
    $replacement = "$Key=$Value"
    $found = $false

    $updated = $lines | ForEach-Object {
        if ($_ -match $pattern) {
            $found = $true
            $replacement
        } else {
            $_
        }
    }

    if (-not $found) {
        $updated += $replacement
    }

    Set-Content -LiteralPath $Path -Value $updated -Encoding UTF8
}

if (Test-Path $envPath) {
    Set-DotEnvValue -Path $envPath -Key 'APP_URL' -Value $localUrl
    Set-DotEnvValue -Path $envPath -Key 'WIBOOST_PUBLIC_URL' -Value ''
    Set-DotEnvValue -Path $envPath -Key 'FILESYSTEM_PUBLIC_URL' -Value '/storage'
}

foreach ($path in @('public/images', 'storage/app/public', 'storage/app/public/categories', 'storage/app/public/products', 'storage/app/public/promos', 'storage/app/public/tutorials')) {
    if (-not (Test-Path $path)) {
        New-Item -ItemType Directory -Path $path | Out-Null
    }
}

$storageLink = Join-Path $projectRoot 'public/storage'
if (-not (Test-Path $storageLink)) {
    php artisan storage:link
}

php artisan optimize:clear
