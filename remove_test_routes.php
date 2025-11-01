<?php

/**
 * Script to remove all test routes from routes/web.php
 */

$filePath = __DIR__ . '/routes/web.php';

if (!file_exists($filePath)) {
    die("File routes/web.php not found!\n");
}

$content = file_get_contents($filePath);
$lines = explode("\n", $content);
$newLines = [];
$skipUntil = null;
$inMultiLineComment = false;

foreach ($lines as $index => $line) {
    $originalLine = $line;
    $trimmedLine = trim($line);
    
    // Skip lines that are part of multi-line comments
    if (strpos($trimmedLine, '/*') !== false) {
        $inMultiLineComment = true;
    }
    if (strpos($trimmedLine, '*/') !== false) {
        $inMultiLineComment = false;
        continue;
    }
    if ($inMultiLineComment) {
        continue;
    }
    
    // Skip single line comments
    if (strpos($trimmedLine, '//') === 0) {
        // Keep comments that are not related to test routes
        if (stripos($trimmedLine, 'test') === false || stripos($trimmedLine, 'Test') === false) {
            $newLines[] = $originalLine;
        }
        continue;
    }
    
    // Skip test route definitions
    if (preg_match('/^Route::(get|post|put|patch|delete)\([\'"]\/test-/', $trimmedLine)) {
        // Check if it's a multi-line route (look ahead)
        $skipNext = false;
        for ($i = $index + 1; $i < min($index + 20, count($lines)); $i++) {
            if (preg_match('/}\);?\s*(->name\([\'"]test\.|->name\([\'"]test-|->name\([\'"]test\.|->middleware|$)/', $lines[$i])) {
                $skipNext = true;
                break;
            }
        }
        continue;
    }
    
    // Skip routes like /hello, /pembayaran-pos, /test-simple
    if (preg_match('/^Route::get\([\'"]\/(hello|pembayaran-pos|test-simple)/', $trimmedLine)) {
        continue;
    }
    
    // Skip routes with test in the name
    if (preg_match('/->name\([\'"]test\./', $trimmedLine)) {
        continue;
    }
    
    // Skip test route sections
    if (stripos($trimmedLine, 'Test ') !== false && stripos($trimmedLine, 'Route') !== false) {
        continue;
    }
    
    // Skip test callback routes
    if (preg_match('/Route::(get|post)\([\'"]\/test-/', $trimmedLine)) {
        continue;
    }
    
    // Skip test controller methods
    if (preg_match('/->test\(\)|->test\w+\(\)/', $trimmedLine)) {
        continue;
    }
    
    $newLines[] = $originalLine;
}

$newContent = implode("\n", $newLines);

// Backup original file
copy($filePath, $filePath . '.backup');

// Write cleaned content
file_put_contents($filePath, $newContent);

echo "Test routes removed from routes/web.php\n";
echo "Backup saved to routes/web.php.backup\n";

