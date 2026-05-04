# Quick Verification Script
# Runs basic checks (single file, overwritten on each run)

# Ensure output directory exists
$outputDir = ".windsurf\skills\task-decomposition\outputs"
if (-not (Test-Path $outputDir)) {
    New-Item -ItemType Directory -Path $outputDir -Force | Out-Null
}

Write-Host "Running Quick Verification" -ForegroundColor Cyan

$issues = @()

# Check PHP syntax
Write-Host "Checking PHP syntax..." -ForegroundColor Yellow
$syntaxErrors = Get-ChildItem -Path "app" -Recurse -Filter "*.php" | ForEach-Object {
    $result = php -l $_.FullName 2>&1
    if ($result -notmatch "No syntax errors") {
        $_.FullName
    }
}

if ($syntaxErrors) {
    Write-Host "Syntax errors found:" -ForegroundColor Red
    $syntaxErrors | ForEach-Object { Write-Host "  $_" }
    $issues += "PHP syntax errors detected"
} else {
    Write-Host "No syntax errors" -ForegroundColor Green
}

# Check if tests exist
Write-Host "Checking test coverage..." -ForegroundColor Yellow
$testCount = Get-ChildItem -Path "tests" -Recurse -Filter "*Test.php" | Measure-Object | Select-Object -ExpandProperty Count
Write-Host "Test files: $testCount" -ForegroundColor Cyan

if ($testCount -eq 0) {
    $issues += "No test files found"
}

# Check composer.json validity
Write-Host "Checking composer.json..." -ForegroundColor Yellow
$composerResult = composer validate 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "composer.json has issues" -ForegroundColor Red
    $issues += "composer.json validation failed"
} else {
    Write-Host "composer.json is valid" -ForegroundColor Green
}

# Save report
$reportFile = Join-Path $outputDir "verification.md"

$reportContent = @"
# Verification Report
Generated: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")

## Issues Found
$($issues | ForEach-Object { "- $_" })

## Summary
- Test files: $testCount
- Syntax errors: $($syntaxErrors.Count)
- Total issues: $($issues.Count)
"@

Set-Content -Path $reportFile -Value $reportContent

Write-Host "`nVerification complete" -ForegroundColor Cyan
Write-Host "Report saved to: $reportFile" -ForegroundColor Cyan
