$ErrorActionPreference = 'Stop'
$runtimePath = Join-Path $PSScriptRoot 'tools\php'
$certPath = (Join-Path $PSScriptRoot 'tools\cacert.pem').Replace('\','/')
$extensionPath = (Join-Path $runtimePath 'ext').Replace('\','/')
$ini = @(
    ('extension_dir="' + $extensionPath + '"'),
    'extension=curl','extension=fileinfo','extension=mbstring','extension=openssl',
    'extension=pdo_sqlite','extension=sqlite3','extension=zip','extension=intl',
    ('curl.cainfo="' + $certPath + '"'), ('openssl.cafile="' + $certPath + '"'),
    'date.timezone=Africa/Casablanca','memory_limit=512M'
)
Set-Content -LiteralPath (Join-Path $runtimePath 'php.ini') -Value $ini
& (Join-Path $runtimePath 'php.exe') -v
