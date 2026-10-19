$ErrorActionPreference = 'Stop'
$runtimePath = Join-Path $PSScriptRoot 'tools\php'
$phpExecutable = Join-Path $runtimePath 'php.exe'
if (-not (Test-Path -LiteralPath $phpExecutable)) { throw 'PHP portable absent. Installez PHP 8.4+ ou restaurez le dossier tools/php.' }
$env:PATH = $runtimePath + ';' + $env:PATH
Push-Location (Join-Path $PSScriptRoot 'backend')
try { & $phpExecutable artisan serve --host=localhost --port=8000 } finally { Pop-Location }
