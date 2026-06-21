#Requires -Version 5.1
$ErrorActionPreference = 'Stop'
Set-Location (Join-Path $PSScriptRoot 'frontend')
npm ci
npm run build
Copy-Item -Force dist/strikescope-form.js ../assets/js/
Copy-Item -Force dist/strikescope-form.css ../assets/css/
Write-Host 'Built and copied strikescope-form.js / strikescope-form.css'
