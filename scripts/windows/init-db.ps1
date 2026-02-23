param(
    [string]$SqlCmdPath = 'sqlcmd',
    [string]$Server = 'localhost',
    [string]$Database = 'profile_program',
    [string]$User = 'sa',
    [string]$Password = 'YourStrong@Passw0rd',
    [switch]$UseTrustedConnection
)

$root = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
$schema = Join-Path $root 'db/schema.sql'
$seed = Join-Path $root 'db/seed_governorates.sql'

if (!(Test-Path $schema)) { throw 'db/schema.sql not found' }
if (!(Test-Path $seed)) { throw 'db/seed_governorates.sql not found' }

$baseArgs = @('-S', $Server, '-d', $Database, '-b', '-C')
if ($UseTrustedConnection) {
    $baseArgs += '-E'
} else {
    $baseArgs += @('-U', $User, '-P', $Password)
}

Write-Host 'Applying schema...' -ForegroundColor Yellow
& $SqlCmdPath @baseArgs '-i' $schema
if ($LASTEXITCODE -ne 0) { throw 'Failed to apply schema.sql' }

Write-Host 'Applying governorates seed...' -ForegroundColor Yellow
& $SqlCmdPath @baseArgs '-i' $seed
if ($LASTEXITCODE -ne 0) { throw 'Failed to apply seed_governorates.sql' }

Write-Host 'Database initialized successfully.' -ForegroundColor Green
