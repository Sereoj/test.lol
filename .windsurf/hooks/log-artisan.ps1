# Log artisan commands for debugging
# Only logs php artisan commands

try {
    $input = $Input | ConvertFrom-Json
    $command = $input.command ?? ''

    if (-not $command) {
        exit 0
    }

    # Only log artisan commands
    if ($command -notmatch 'artisan\s+') {
        exit 0
    }

    $logEntry = @{
        timestamp = (Get-Date).ToString("o")
        command = $command
        cwd = $input.cwd ?? (Get-Location).Path
    } | ConvertTo-Json

    $logFile = ".windsurf\hooks\artisan-log.jsonl"
    $logDir = Split-Path $logFile -Parent

    if (-not (Test-Path $logDir)) {
        New-Item -ItemType Directory -Path $logDir -Force | Out-Null
    }

    Add-Content -Path $logFile -Value $logEntry

    exit 0
} catch {
    Write-Error "[ERROR] Failed to log artisan command: $_"
    exit 0
}
