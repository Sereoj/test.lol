# Quick Task Breakdown Script
# Creates a structured task breakdown (single file, overwritten on each run)

param(
    [Parameter(Mandatory=$true)]
    [string]$TaskName
)

# Ensure output directory exists
$outputDir = ".windsurf\skills\task-decomposition\outputs"
if (-not (Test-Path $outputDir)) {
    New-Item -ItemType Directory -Path $outputDir -Force | Out-Null
}

$breakdownFile = Join-Path $outputDir "breakdown.md"

$content = @"
# Task Breakdown: $TaskName
Created: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")

## Main Task
$TaskName

## Subtasks
- [ ] Analyze requirements
- [ ] Check existing code
- [ ] Design solution
- [ ] Implement
- [ ] Test
- [ ] Document

## Dependencies
- None identified yet

## Complexity Assessment
- Estimated time: TBD
- Risk level: TBD
"@

Set-Content -Path $breakdownFile -Value $content

Write-Host "Breakdown created: $breakdownFile" -ForegroundColor Green
Write-Host "Edit the file to add specific subtasks" -ForegroundColor Yellow
