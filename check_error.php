<?php
/**
 * Check Error Log - View Laravel errors
 * Upload file ini ke root folder VPS dan akses via browser
 */

$logFile = __DIR__ . '/storage/logs/laravel.log';

echo "<pre>";
echo "================================================\n";
echo "📋 Laravel Error Log - Last 100 Lines\n";
echo "================================================\n\n";

if (!file_exists($logFile)) {
    echo "❌ Log file not found: $logFile\n";
    exit;
}

echo "📁 Log file: $logFile\n";
echo "📏 File size: " . number_format(filesize($logFile)) . " bytes\n";
echo "🕐 Last modified: " . date('Y-m-d H:i:s', filemtime($logFile)) . "\n\n";

echo "================================================\n";
echo "Recent Errors:\n";
echo "================================================\n\n";

// Read last 100 lines
$lines = file($logFile);
$totalLines = count($lines);
$lastLines = array_slice($lines, -100);

foreach ($lastLines as $line) {
    // Highlight errors
    if (stripos($line, 'ERROR') !== false) {
        echo "<span style='color:red;font-weight:bold;'>$line</span>";
    } elseif (stripos($line, 'WARNING') !== false) {
        echo "<span style='color:orange;'>$line</span>";
    } elseif (stripos($line, 'cart') !== false || stripos($line, 'ipaymu') !== false) {
        echo "<span style='color:blue;'>$line</span>";
    } else {
        echo $line;
    }
}

echo "\n================================================\n";
echo "Total lines in log: $totalLines\n";
echo "Showing last: 100 lines\n";
echo "================================================\n\n";

echo "📝 Actions:\n";
echo "1. <a href='?clear=1'>Clear log file</a>\n";
echo "2. <a href='?download=1'>Download full log</a>\n";
echo "3. <a href='/clear_cache_vps.php'>Clear cache</a>\n";
echo "4. <a href='/student/cart'>Test cart payment</a>\n";

// Handle clear log
if (isset($_GET['clear'])) {
    file_put_contents($logFile, '');
    echo "\n✅ Log file cleared!\n";
    echo "<a href='check_error.php'>Refresh</a>\n";
}

// Handle download
if (isset($_GET['download'])) {
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="laravel.log"');
    readfile($logFile);
    exit;
}

echo "</pre>";

