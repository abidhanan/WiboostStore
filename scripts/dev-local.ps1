param(
    [int] $Port = 8000
)

$ErrorActionPreference = 'Stop'

$projectRoot = Resolve-Path (Join-Path $PSScriptRoot '..')
Set-Location $projectRoot

$localUrl = "http://127.0.0.1:$Port"

& (Join-Path $PSScriptRoot 'prepare-local.ps1') -Port $Port

Write-Host ''
Write-Host 'Wiboost local siap. Ctrl+klik link di bawah ini:'
Write-Host $localUrl
Write-Host ''
Write-Host "Menjalankan Laravel server dan Vite. Tekan Ctrl+C untuk berhenti."

npx concurrently -c "#93c5fd,#fdba74" "php artisan serve --host=127.0.0.1 --port=$Port" "npm run vite" --names=server,vite --kill-others
