# Block changes to vendor, node_modules, storage
# Prevents Cascade from modifying generated directories

try {
    $input = $Input | ConvertFrom-Json
    $filePath = $input.file_path ?? $input.path

    if (-not $filePath) {
        exit 0
    }

    # Normalize path for comparison
    $normalizedPath = $filePath -replace '\\', '/'

    # Directories that should not be manually modified
    $blockedPatterns = @(
        '^vendor/',
        '^node_modules/',
        '^storage/',
        '^bootstrap/cache/',
        '^public/vendor/',
        '^public/build/'
    )

    $isBlocked = $blockedPatterns | Where-Object { $normalizedPath -like "$_*" }

    if ($isBlocked) {
        Write-Error "[BLOCKED] Changes to generated directory denied: $filePath"
        exit 2
    }

    exit 0
} catch {
    Write-Error "[ERROR] Failed to parse hook input: $_"
    exit 0
}
