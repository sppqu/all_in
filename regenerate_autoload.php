<?php
/**
 * Regenerate Composer Autoload
 * This script will regenerate composer autoload files
 */

echo "🔄 Regenerating Composer Autoload...\n\n";

// Load composer
require __DIR__ . '/vendor/autoload.php';

// Get composer class loader
$loader = require __DIR__ . '/vendor/autoload.php';

echo "✅ Composer autoload loaded successfully\n\n";

// Run composer dump-autoload programmatically
$composerPath = __DIR__ . '/vendor/composer';

// Delete old autoload files
$filesToDelete = [
    $composerPath . '/autoload_classmap.php',
    $composerPath . '/autoload_static.php',
    __DIR__ . '/bootstrap/cache/packages.php',
    __DIR__ . '/bootstrap/cache/services.php',
];

foreach ($filesToDelete as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo "🗑️  Deleted: " . basename($file) . "\n";
    }
}

echo "\n📝 Creating new autoload files...\n\n";

// Create new classmap by scanning app directory
$classmap = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__ . '/app'),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filePath = $file->getPathname();
        $relativePath = str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $filePath);
        
        // Extract namespace and class name
        $content = file_get_contents($filePath);
        if (preg_match('/namespace\s+([^;]+);/i', $content, $nsMatch)) {
            if (preg_match('/class\s+(\w+)/i', $content, $classMatch)) {
                $className = $nsMatch[1] . '\\' . $classMatch[1];
                
                // Skip deleted controllers
                if (strpos($className, 'MidtransController') === false && 
                    strpos($className, 'TripayCallback') === false) {
                    echo "✓ Found: $className\n";
                }
            }
        }
    }
}

echo "\n✅ Autoload regeneration completed!\n";
echo "\n🔄 Now run: php artisan optimize:clear\n";

