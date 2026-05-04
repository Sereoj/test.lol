# Quick Code Analysis Script
# Fast search for patterns, functions, TODOs in the codebase

param(
    [Parameter(Mandatory=$true)]
    [string]$SearchPattern,
    [Parameter(Mandatory=$false)]
    [ValidateSet("pattern", "function", "todo", "class", "logs")]
    [string]$SearchType = "pattern"
)

Write-Host "Searching for: $SearchPattern (type: $SearchType)" -ForegroundColor Cyan

# Build search pattern based on type
switch ($SearchType) {
    "function" {
        $pattern = "function\s+$SearchPattern"
        Write-Host "Searching for function: $SearchPattern" -ForegroundColor Cyan
    }
    "todo" {
        $pattern = "(TODO|FIXME|XXX|HACK).*$SearchPattern"
        Write-Host "Searching for TODOs containing: $SearchPattern" -ForegroundColor Cyan
    }
    "class" {
        $pattern = "class\s+$SearchPattern"
        Write-Host "Searching for class: $SearchPattern" -ForegroundColor Cyan
    }
    "logs" {
        $pattern = $SearchPattern
        Write-Host "Searching in logs for: $SearchPattern" -ForegroundColor Cyan
    }
    default {
        $pattern = $SearchPattern
    }
}

# Search in app directory
$appResults = Get-ChildItem -Path "app" -Recurse -Filter "*.php" | Select-String -Pattern $pattern -List

if ($appResults) {
    Write-Host "Found in app/:" -ForegroundColor Green
    $appResults | ForEach-Object {
        Write-Host "  $($_.Path):$($_.LineNumber)" -ForegroundColor Yellow
    }
}

# Search in config directory
$configResults = Get-ChildItem -Path "config" -Recurse -Filter "*.php" | Select-String -Pattern $pattern -List

if ($configResults) {
    Write-Host "Found in config/:" -ForegroundColor Green
    $configResults | ForEach-Object {
        Write-Host "  $($_.Path):$($_.LineNumber)" -ForegroundColor Yellow
    }
}

# Search in routes
$routeResults = Get-ChildItem -Path "routes" -Recurse -Filter "*.php" | Select-String -Pattern $pattern -List

if ($routeResults) {
    Write-Host "Found in routes/:" -ForegroundColor Green
    $routeResults | ForEach-Object {
        Write-Host "  $($_.Path):$($_.LineNumber)" -ForegroundColor Yellow
    }
}

# Search in logs if specified
if ($SearchType -eq "logs") {
    $logResults = @()

    # Search in storage/logs
    if (Test-Path "storage/logs") {
        $storageLogs = Get-ChildItem -Path "storage/logs" -Filter "*.log" | Select-String -Pattern $pattern -List
        if ($storageLogs) {
            Write-Host "Found in storage/logs/:" -ForegroundColor Green
            $storageLogs | ForEach-Object {
                Write-Host "  $($_.Path):$($_.LineNumber)" -ForegroundColor Yellow
            }
            $logResults += $storageLogs
        }
    }

    # Search in .windsurf hooks logs
    if (Test-Path ".windsurf/hooks") {
        $windsurfLogs = Get-ChildItem -Path ".windsurf/hooks" -Filter "*.jsonl" | Select-String -Pattern $pattern -List
        if ($windsurfLogs) {
            Write-Host "Found in .windsurf/hooks/:" -ForegroundColor Green
            $windsurfLogs | ForEach-Object {
                Write-Host "  $($_.Path):$($_.LineNumber)" -ForegroundColor Yellow
            }
            $logResults += $windsurfLogs
        }
    }

    if ($logResults.Count -eq 0) {
        Write-Host "No matches found in logs" -ForegroundColor Red
    }
}

if (-not ($appResults -or $configResults -or $routeResults)) {
    Write-Host "No matches found" -ForegroundColor Red
}
