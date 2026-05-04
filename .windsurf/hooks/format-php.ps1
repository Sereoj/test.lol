# Format PHP files with Laravel Pint
# Only runs for .php files in app/, database/, config/, routes/

try {
    $input = $Input | ConvertFrom-Json
    $filePath = $input.file_path ?? $input.path

    if (-not $filePath) {
        exit 0
    }

    # Only process .php files
    if (-not ($filePath -match '\.php$')) {
        exit 0
    }

    # Normalize path
    $normalizedPath = $filePath -replace '\\', '/'

    # Only format files in specific directories
    $targetDirectories = @(
        '^app/',
        '^database/',
        '^config/',
        '^routes/'
    )

    $shouldFormat = $targetDirectories | Where-Object { $normalizedPath -like "$_*" }

    if (-not $shouldFormat) {
        exit 0
    }

    # Run Laravel Pint on the specific file
    $result = & composer run pint -- $filePath 2>&1

    if ($LASTEXITCODE -ne 0) {
        Write-Error "[WARN] Laravel Pint failed for $filePath"
    }

    exit 0
} catch {
    Write-Error "[ERROR] Failed to format PHP file: $_"
    exit 0
}
