# Run Laravel Pint in test mode
$ErrorActionPreference = "Stop"
$rootDir = (Resolve-Path "$PSScriptRoot\..\..\..\..").Path
Set-Location $rootDir

Write-Host "=== Laravel Pint (Test Mode) ===" -ForegroundColor Cyan
& ".\vendor\bin\pint.bat" --test
Write-Host "Selesai." -ForegroundColor Green
