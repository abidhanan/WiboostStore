$ErrorActionPreference = 'Stop'

$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$tempRoot = [IO.Path]::GetFullPath($env:TEMP).TrimEnd('\') + '\'
$staging = Join-Path $env:TEMP ('wiboost-deploy-staging-' + [guid]::NewGuid().ToString('N'))
$zipPath = Join-Path $projectRoot 'wibooststore-domainesia-revisi-final-20260612.zip'

function Remove-TemporaryTree([string] $Path) {
    if (-not (Test-Path -LiteralPath $Path)) {
        return
    }

    $resolvedPath = [IO.Path]::GetFullPath($Path)

    if (-not $resolvedPath.StartsWith($tempRoot, [StringComparison]::OrdinalIgnoreCase)) {
        throw "Menolak menghapus path di luar folder temporary: $resolvedPath"
    }

    $emptyDirectory = Join-Path $env:TEMP ('wiboost-empty-' + [guid]::NewGuid().ToString('N'))
    New-Item -ItemType Directory -Path $emptyDirectory | Out-Null
    & robocopy $emptyDirectory $resolvedPath /MIR /NFL /NDL /NJH /NJS /NC /NS /NP | Out-Null

    if ($LASTEXITCODE -gt 7) {
        throw "Gagal membersihkan staging temporary: $resolvedPath"
    }

    Remove-Item -LiteralPath $emptyDirectory -Recurse -Force
    Remove-Item -LiteralPath $resolvedPath -Recurse -Force
}

function Invoke-Robocopy {
    param(
        [string] $Source,
        [string] $Destination,
        [string[]] $ExtraArguments = @()
    )

    New-Item -ItemType Directory -Force -Path $Destination | Out-Null
    & robocopy $Source $Destination /E /NFL /NDL /NJH /NJS /NC /NS /NP @ExtraArguments | Out-Null

    if ($LASTEXITCODE -gt 7) {
        throw "Robocopy gagal dengan exit code $LASTEXITCODE saat menyalin $Source"
    }
}

Set-Location $projectRoot
Remove-TemporaryTree $staging

if (Test-Path -LiteralPath $zipPath) {
    Remove-Item -LiteralPath $zipPath -Force
}

if (-not (Test-Path -LiteralPath (Join-Path $projectRoot 'public\build\manifest.json'))) {
    throw 'public/build belum ada. Jalankan: npm run build'
}

if (-not (Test-Path -LiteralPath (Join-Path $projectRoot 'vendor\autoload.php'))) {
    throw 'vendor belum ada. Jalankan: composer install --no-dev --optimize-autoloader'
}

Write-Host 'Menyalin source workspace terbaru...'
Invoke-Robocopy $projectRoot $staging @(
    '/XD',
    (Join-Path $projectRoot '.git'),
    (Join-Path $projectRoot '.idea'),
    (Join-Path $projectRoot '.vscode'),
    (Join-Path $projectRoot '.codex_zip'),
    (Join-Path $projectRoot 'node_modules'),
    (Join-Path $projectRoot 'vendor'),
    (Join-Path $projectRoot 'public\build'),
    (Join-Path $projectRoot 'public\hot'),
    (Join-Path $projectRoot 'public\storage'),
    'deploy-*',
    (Join-Path $projectRoot 'storage\framework\cache'),
    (Join-Path $projectRoot 'storage\framework\sessions'),
    (Join-Path $projectRoot 'storage\framework\views'),
    (Join-Path $projectRoot 'storage\logs'),
    '/XF',
    '.env',
    '.env.backup',
    '.env.production',
    '.phpunit.result.cache',
    '*.zip',
    'database.sqlite',
    'browser-check.sqlite',
    'xoftware_docs.html'
)

Write-Host 'Menyalin dependency production dan hasil build...'
Invoke-Robocopy (Join-Path $projectRoot 'vendor') (Join-Path $staging 'vendor')
Invoke-Robocopy (Join-Path $projectRoot 'public\build') (Join-Path $staging 'public\build')

$runtimeDirectories = @(
    'storage\framework\cache\data',
    'storage\framework\sessions',
    'storage\framework\views',
    'storage\logs',
    'bootstrap\cache'
)

foreach ($relativePath in $runtimeDirectories) {
    New-Item -ItemType Directory -Force -Path (Join-Path $staging $relativePath) | Out-Null
}

Write-Host 'Membuat ZIP deploy Domainesia...'
$sevenZip = (Get-Command 7z -ErrorAction SilentlyContinue).Source

if (-not $sevenZip) {
    throw '7z tidak ditemukan. Pastikan Laragon atau 7-Zip tersedia di PATH.'
}

Push-Location $staging
try {
    & $sevenZip a -tzip -mx=3 -bd -y $zipPath '.\*' | Out-Null

    if ($LASTEXITCODE -ne 0) {
        throw "7-Zip gagal dengan exit code $LASTEXITCODE"
    }
} finally {
    Pop-Location
}

$sizeMB = [math]::Round((Get-Item -LiteralPath $zipPath).Length / 1MB, 2)
Write-Host "ZIP siap: $zipPath"
Write-Host "Ukuran: $sizeMB MB"

Remove-TemporaryTree $staging
