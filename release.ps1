#Requires -Version 5.1
$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot

$versionLine = Select-String -Path 'strikescope-wp.php' -Pattern '^\s*\*\s*Version:\s*(\S+)' | Select-Object -First 1
if (-not $versionLine) {
    throw 'Could not read Version from strikescope-wp.php'
}
$version = $versionLine.Matches[0].Groups[1].Value
$zip = "strikescope-wp-$version.zip"

if (Test-Path $zip) {
    Remove-Item -Force $zip
}

$paths = @(
    'assets',
    'config',
    'docs',
    'includes',
    'languages',
    'templates',
    'admin',
    'wp-org-assets',
    'readme.txt',
    'README.md',
    'CHANGELOG.md',
    'LICENSE',
    'strikescope-wp.php',
    'uninstall.php'
)

Compress-Archive -Path $paths -DestinationPath $zip -Force
Write-Host "Created $zip"
