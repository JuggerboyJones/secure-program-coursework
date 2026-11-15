$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
Push-Location -LiteralPath $projectRoot

try {
    if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
        throw 'Docker Desktop is not installed or is not available in this terminal.'
    }

    $settingsPath = Join-Path $projectRoot '.env'
    if (-not (Test-Path -LiteralPath $settingsPath)) {
        $randomGenerator = [System.Security.Cryptography.RandomNumberGenerator]::Create()
        try {
            $appBytes = New-Object byte[] 24
            $rootBytes = New-Object byte[] 24
            $randomGenerator.GetBytes($appBytes)
            $randomGenerator.GetBytes($rootBytes)
            $appPassword = [BitConverter]::ToString($appBytes).Replace('-', '').ToLowerInvariant()
            $rootPassword = [BitConverter]::ToString($rootBytes).Replace('-', '').ToLowerInvariant()
        }
        finally {
            $randomGenerator.Dispose()
        }

        $settings = @(
            'DB_DATABASE=incident_reporting'
            'DB_USERNAME=incident_app'
            "DB_PASSWORD=$appPassword"
            "DB_ROOT_PASSWORD=$rootPassword"
            'WEB_PORT=8080'
        ) -join [Environment]::NewLine
        [IO.File]::WriteAllText($settingsPath, $settings + [Environment]::NewLine, (New-Object Text.UTF8Encoding($false)))
        Write-Host 'Created private local database settings.'
    }

    & docker compose up -d --build --wait --wait-timeout 180
    if ($LASTEXITCODE -ne 0) {
        throw 'The project did not start. Check that Docker Desktop is running and review the message above.'
    }

    & docker compose exec -T web php scripts/check-setup.php
    if ($LASTEXITCODE -ne 0) {
        throw 'The project started, but the database check failed.'
    }

    $portLine = Get-Content -LiteralPath $settingsPath | Where-Object { $_ -match '^WEB_PORT=\d+$' } | Select-Object -Last 1
    $projectPort = if ($portLine) { $portLine.Split('=', 2)[1] } else { '8080' }
    Write-Host "Open http://localhost:$projectPort in your browser."
}
finally {
    Pop-Location
}
