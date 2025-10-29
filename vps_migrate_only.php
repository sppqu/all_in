<?php

/**
 * VPS Migration Runner (Non-Interactive)
 * Run: php vps_migrate_only.php
 * 
 * This script just runs migrations without asking questions.
 * Use this after database connection is already fixed.
 */

echo "🚀 VPS Migration Runner\n";
echo "========================\n\n";

// Check if we're in the right directory
if (!file_exists('artisan')) {
    echo "❌ Error: artisan file not found!\n";
    echo "Please run this script from the project root directory.\n";
    exit(1);
}

// Check if .env exists
if (!file_exists('.env')) {
    echo "❌ Error: .env file not found!\n";
    exit(1);
}

echo "📄 .env file found\n";
echo "🔍 Testing database connection...\n\n";

// Load .env
$envContent = file_get_contents('.env');
preg_match('/DB_HOST=(.*)/', $envContent, $host);
preg_match('/DB_DATABASE=(.*)/', $envContent, $database);
preg_match('/DB_USERNAME=(.*)/', $envContent, $username);
preg_match('/DB_PASSWORD=(.*)/', $envContent, $password);

$dbHost = trim($host[1] ?? '127.0.0.1');
$dbName = trim($database[1] ?? '');
$dbUser = trim($username[1] ?? '');
$dbPass = trim($password[1] ?? '');

if (empty($dbName)) {
    echo "❌ Error: DB_DATABASE not configured in .env\n";
    exit(1);
}

// Test connection
try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName}",
        $dbUser,
        $dbPass
    );
    
    echo "✅ Database connection successful!\n";
    echo "📊 Database: {$dbName}\n\n";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "Please fix database configuration first using:\n";
    echo "  php vps_fix_database.php\n\n";
    exit(1);
}

// Clear cache
echo "🧹 Clearing cache...\n";
exec('php artisan config:clear 2>&1', $output1);
exec('php artisan cache:clear 2>&1', $output2);
echo "✅ Cache cleared\n\n";

// Run migrations
echo "🚀 Running migrations...\n";
echo "========================\n\n";

passthru('php artisan migrate --force', $returnCode);

if ($returnCode === 0) {
    echo "\n✅ Migration completed successfully!\n\n";
    
    // Show table count
    try {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "📋 Total tables in database: " . count($tables) . "\n";
    } catch (PDOException $e) {
        // Ignore
    }
} else {
    echo "\n❌ Migration failed with exit code: {$returnCode}\n";
    exit(1);
}

echo "\n✅ Done!\n";
echo "\nNext steps:\n";
echo "  1. Restart PHP-FPM: systemctl restart php8.2-fpm\n";
echo "  2. Test website: https://srx.sppqu.my.id\n";

