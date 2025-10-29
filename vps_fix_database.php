<?php

/**
 * VPS Database Connection Fix Script
 * Run: php vps_fix_database.php
 */

echo "🔧 VPS Database Connection Fix\n";
echo "================================\n\n";

// Check if .env exists
if (!file_exists('.env')) {
    echo "❌ Error: .env file not found!\n";
    echo "Please create .env file first by copying .env.example\n";
    exit(1);
}

// Read current .env
$envContent = file_get_contents('.env');
echo "📄 Current .env file found\n\n";

// Parse current DB settings
preg_match('/DB_CONNECTION=(.*)/', $envContent, $connection);
preg_match('/DB_HOST=(.*)/', $envContent, $host);
preg_match('/DB_PORT=(.*)/', $envContent, $port);
preg_match('/DB_DATABASE=(.*)/', $envContent, $database);
preg_match('/DB_USERNAME=(.*)/', $envContent, $username);
preg_match('/DB_PASSWORD=(.*)/', $envContent, $password);

echo "📊 Current Database Settings:\n";
echo "-----------------------------\n";
echo "Connection: " . ($connection[1] ?? 'Not set') . "\n";
echo "Host: " . ($host[1] ?? 'Not set') . "\n";
echo "Port: " . ($port[1] ?? 'Not set') . "\n";
echo "Database: " . ($database[1] ?? 'Not set') . "\n";
echo "Username: " . ($username[1] ?? 'Not set') . "\n";
echo "Password: " . (empty($password[1]) ? '(empty)' : '***') . "\n\n";

// Interactive mode
echo "🔧 Fix Database Configuration\n";
echo "-----------------------------\n";
echo "Enter new values (press Enter to keep current):\n\n";

// Function to read input (compatible with all PHP versions)
function readInput($prompt) {
    echo $prompt;
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    return trim($line);
}

// Get new values
$newDatabase = readInput("Database Name [{$database[1]}]: ");
$newDatabase = $newDatabase ?: ($database[1] ?? '');

$newUsername = readInput("Database Username [{$username[1]}]: ");
$newUsername = $newUsername ?: ($username[1] ?? '');

$newPassword = readInput("Database Password: ");
if (empty($newPassword) && !empty($password[1])) {
    $keepPassword = readInput("Keep current password? (y/n): ");
    if (strtolower($keepPassword) === 'y') {
        $newPassword = $password[1];
    }
}

// Update .env content
$newEnvContent = preg_replace('/DB_CONNECTION=.*/', 'DB_CONNECTION=mysql', $envContent);
$newEnvContent = preg_replace('/DB_HOST=.*/', 'DB_HOST=127.0.0.1', $newEnvContent);
$newEnvContent = preg_replace('/DB_PORT=.*/', 'DB_PORT=3306', $newEnvContent);
$newEnvContent = preg_replace('/DB_DATABASE=.*/', "DB_DATABASE={$newDatabase}", $newEnvContent);
$newEnvContent = preg_replace('/DB_USERNAME=.*/', "DB_USERNAME={$newUsername}", $newEnvContent);
$newEnvContent = preg_replace('/DB_PASSWORD=.*/', "DB_PASSWORD={$newPassword}", $newEnvContent);

// Backup current .env
$backupFile = '.env.backup.' . date('YmdHis');
copy('.env', $backupFile);
echo "\n✅ Backed up .env to: {$backupFile}\n";

// Write new .env
file_put_contents('.env', $newEnvContent);
echo "✅ Updated .env file\n\n";

// Clear cache
echo "🧹 Clearing cache...\n";
exec('php artisan config:clear 2>&1', $output1, $return1);
exec('php artisan cache:clear 2>&1', $output2, $return2);
echo "✅ Cache cleared\n\n";

// Test database connection
echo "🔍 Testing database connection...\n";
echo "-----------------------------\n";

try {
    $pdo = new PDO(
        "mysql:host=127.0.0.1;dbname={$newDatabase}",
        $newUsername,
        $newPassword
    );
    
    echo "✅ Database connection successful!\n\n";
    
    // Show database info
    $stmt = $pdo->query("SELECT DATABASE() as db, VERSION() as version");
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "📊 Database Information:\n";
    echo "Database: {$info['db']}\n";
    echo "Version: {$info['version']}\n\n";
    
    // Check tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📋 Tables in database: " . count($tables) . "\n";
    if (count($tables) > 0) {
        echo "Sample tables: " . implode(', ', array_slice($tables, 0, 5)) . "\n";
    }
    echo "\n";
    
    // Ask to run migrations
    $runMigrate = readInput("Run migrations now? (y/n): ");
    if (strtolower($runMigrate) === 'y') {
        echo "\n🚀 Running migrations...\n";
        echo "-----------------------------\n";
        passthru('php artisan migrate --force');
        echo "\n✅ Migrations completed!\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    
    echo "💡 Common issues:\n";
    echo "1. Wrong database name\n";
    echo "2. Wrong username/password\n";
    echo "3. MySQL service not running\n";
    echo "4. Database doesn't exist\n\n";
    
    echo "Try these commands:\n";
    echo "1. Check MySQL status: systemctl status mysql\n";
    echo "2. Create database: mysql -u root -p -e \"CREATE DATABASE {$newDatabase};\"\n";
    echo "3. Check user permissions: mysql -u root -p -e \"SHOW GRANTS FOR '{$newUsername}'@'localhost';\"\n";
}

echo "\n✅ Done!\n";

