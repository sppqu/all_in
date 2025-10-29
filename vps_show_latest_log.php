<?php
/**
 * Script untuk menampilkan log terbaru
 * Upload ke root VPS, akses via browser: https://srx.sppqu.my.id/vps_show_latest_log.php
 */

$logPath = __DIR__ . '/storage/logs/laravel.log';

if (!file_exists($logPath)) {
    die("❌ Log file tidak ditemukan: " . $logPath);
}

// Get last 200 lines
$lines = file($logPath);
$lastLines = array_slice($lines, -200);

echo "<pre style='background: #1e1e1e; color: #d4d4d4; padding: 20px; font-family: monospace; font-size: 12px;'>";
echo "=======================================================\n";
echo "   LATEST LARAVEL LOG (Last 200 lines)\n";
echo "=======================================================\n\n";

foreach ($lastLines as $line) {
    // Highlight important keywords
    if (str_contains($line, 'full_response')) {
        echo "<span style='color: #4ec9b0; font-weight: bold;'>" . htmlspecialchars($line) . "</span>";
    } elseif (str_contains($line, 'ERROR')) {
        echo "<span style='color: #f48771;'>" . htmlspecialchars($line) . "</span>";
    } elseif (str_contains($line, 'iPaymu')) {
        echo "<span style='color: #dcdcaa;'>" . htmlspecialchars($line) . "</span>";
    } elseif (str_contains($line, 'payment_url')) {
        echo "<span style='color: #ce9178; font-weight: bold;'>" . htmlspecialchars($line) . "</span>";
    } else {
        echo htmlspecialchars($line);
    }
}

echo "</pre>";

// Also show last error specifically
echo "<hr>";
echo "<h3>🔍 Filter: Lines with 'full_response':</h3>";
echo "<pre style='background: #1e1e1e; color: #4ec9b0; padding: 20px; font-family: monospace; font-size: 12px;'>";

foreach ($lastLines as $line) {
    if (str_contains($line, 'full_response')) {
        echo htmlspecialchars($line);
    }
}

echo "</pre>";

