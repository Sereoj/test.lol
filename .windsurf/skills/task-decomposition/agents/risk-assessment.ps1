# Quick Risk Assessment Script
# Checks for potential risks (single file, overwritten on each run)

# Ensure output directory exists
$outputDir = ".windsurf\skills\task-decomposition\outputs"
if (-not (Test-Path $outputDir)) {
    New-Item -ItemType Directory -Path $outputDir -Force | Out-Null
}

Write-Host "Risk Assessment Check" -ForegroundColor Cyan

$risks = @()

# Check for missing .gitignore entries
if (-not (Get-Content ".gitignore" | Select-String ".env")) {
    $risks += ".env not in .gitignore - potential credential exposure"
}

# Check for large files
$largeFiles = Get-ChildItem -Path "." -Recurse -File | Where-Object { $_.Length -gt 10MB }
if ($largeFiles) {
    $risks += "Large files found that may impact performance"
}

# Check for uncommitted changes
$gitStatus = git status --porcelain 2>$null
if ($gitStatus) {
    $risks += "Uncommitted changes exist - potential conflicts"
}

# Check for missing tests
$appFiles = Get-ChildItem -Path "app" -Recurse -Filter "*.php" | Measure-Object | Select-Object -ExpandProperty Count
$testFiles = Get-ChildItem -Path "tests" -Recurse -Filter "*Test.php" | Measure-Object | Select-Object -ExpandProperty Count

if ($appFiles -gt 0 -and $testFiles -eq 0) {
    $risks += "No test files found - low test coverage"
}

# Save report
$reportFile = Join-Path $outputDir "risk-assessment.md"

$reportContent = @"
# Risk Assessment Report
Generated: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")

## Detected Risks
$($risks | ForEach-Object { "- $_" })

## Summary
Total risks detected: $($risks.Count)
"@

Set-Content -Path $reportFile -Value $reportContent

if ($risks.Count -eq 0) {
    Write-Host "No obvious risks detected" -ForegroundColor Green
} else {
    Write-Host "Potential risks detected:" -ForegroundColor Yellow
    $risks | ForEach-Object { Write-Host "  - $_" -ForegroundColor Red }
}

Write-Host "Report saved to: $reportFile" -ForegroundColor Cyan
