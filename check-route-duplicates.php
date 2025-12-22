#!/usr/bin/env php
<?php

function findRouteFiles($dir) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir)
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }

    return $files;
}

function extractRouteNames($content) {
    $names = [];
    preg_match_all('/->name\([\'"]([^\'"]+)[\'"]\)/m', $content, $matches, PREG_OFFSET_CAPTURE);

    foreach ($matches[1] as $index => $match) {
        $name = $match[0];
        $position = $match[1];

        // Find line number
        $lineNumber = substr_count(substr($content, 0, $position), "\n") + 1;
        $names[] = [
            'name' => $name,
            'line' => $lineNumber
        ];
    }

    return $names;
}

echo "========================================\n";
echo "Checking for duplicate route names...\n";
echo "========================================\n\n";

$routesDir = __DIR__ . '/routes';
$files = findRouteFiles($routesDir);

$allRoutes = [];
$duplicates = [];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $routeNames = extractRouteNames($content);

    foreach ($routeNames as $route) {
        $name = $route['name'];

        if (!isset($allRoutes[$name])) {
            $allRoutes[$name] = [];
        }

        $allRoutes[$name][] = [
            'file' => str_replace(__DIR__ . '/', '', $file),
            'line' => $route['line']
        ];
    }
}

// Find duplicates
foreach ($allRoutes as $name => $locations) {
    if (count($locations) > 1) {
        $duplicates[$name] = $locations;
    }
}

if (empty($duplicates)) {
    echo "✅ No duplicate route names found!\n";
    echo "Total unique routes: " . count($allRoutes) . "\n";
    exit(0);
}

echo "❌ Found " . count($duplicates) . " duplicate route name(s):\n\n";

foreach ($duplicates as $name => $locations) {
    echo "Route name: \"{$name}\" (used " . count($locations) . " times)\n";
    foreach ($locations as $location) {
        echo "  📄 {$location['file']}:{$location['line']}\n";
    }
    echo "\n";
}

echo "========================================\n";
echo "Total routes: " . array_sum(array_map('count', $allRoutes)) . "\n";
echo "Unique routes: " . count($allRoutes) . "\n";
echo "Duplicates: " . count($duplicates) . "\n";
echo "========================================\n";

exit(1);
