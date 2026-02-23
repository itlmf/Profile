param(
    [string]$DbHost = 'localhost',
    [string]$DbPort = '1433',
    [string]$DbName = 'profile_program',
    [string]$DbUser = 'sa',
    [string]$DbPass = 'YourStrong@Passw0rd',
    [string]$AppLocale = 'ar',
    [string]$AppBaseUrl = 'http://localhost'
)

$root = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
$envExample = Join-Path $root '.env.example'
$envFile = Join-Path $root '.env'

if (!(Test-Path $envExample)) {
    throw '.env.example not found.'
}

Copy-Item $envExample $envFile -Force

$content = Get-Content $envFile -Raw
$content = $content -replace '(?m)^APP_LOCALE=.*$', "APP_LOCALE=$AppLocale"
$content = $content -replace '(?m)^APP_BASE_URL=.*$', "APP_BASE_URL=$AppBaseUrl"
$content = $content -replace '(?m)^DB_HOST=.*$', "DB_HOST=$DbHost"
$content = $content -replace '(?m)^DB_PORT=.*$', "DB_PORT=$DbPort"
$content = $content -replace '(?m)^DB_NAME=.*$', "DB_NAME=$DbName"
$content = $content -replace '(?m)^DB_USER=.*$', "DB_USER=$DbUser"
$content = $content -replace '(?m)^DB_PASS=.*$', "DB_PASS=$DbPass"
Set-Content -Path $envFile -Value $content -Encoding UTF8

Write-Host '.env created successfully at:' $envFile -ForegroundColor Green
