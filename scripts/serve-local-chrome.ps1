param(
    [int] $Port = 8000
)

$ErrorActionPreference = 'Stop'

$projectRoot = Resolve-Path (Join-Path $PSScriptRoot '..')
Set-Location $projectRoot

$localUrl = "http://127.0.0.1:$Port"
& (Join-Path $PSScriptRoot 'prepare-local.ps1') -Port $Port
& (Join-Path $PSScriptRoot 'open-local-chrome.ps1') -Url $localUrl

Write-Host ''
Write-Host 'Wiboost local akan dibuka di Chrome. Link lokal:'
Write-Host $localUrl
Write-Host ''
Write-Host "Tekan Ctrl+C untuk menghentikan server."

php artisan serve --host=127.0.0.1 --port=$Port
