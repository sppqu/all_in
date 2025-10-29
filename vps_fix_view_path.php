<?php

/**
 * Fix View Path Not Found Error
 * Run: php vps_fix_view_path.php
 * 
 * Creates missing view cache directories
 */

echo "🔧 Fix View Path Not Found\n";
echo "==========================\n\n";

// Define paths
$storagePath = __DIR__ . '/storage/framework/views';
$cachePath = __DIR__ . '/storage/framework/cache';
$sessionsPath = __DIR__ . '/storage/framework/sessions';

$paths = [
    'Views' => $storagePath,
    'Cache' => $cachePath,
    'Sessions' => $sessionsPath,
];

echo "📂 Checking storage directories...\n\n";

foreach ($paths as $name => $path) {
    if (!is_dir($path)) {
        echo "❌ {$name} directory not found: {$path}\n";
        echo "   Creating directory...\n";
        
        if (mkdir($path, 0755, true)) {
            echo "   ✅ Directory created\n";
        } else {
            echo "   ❌ Failed to create directory\n";
        }
    } else {
        echo "✅ {$name} directory exists: {$path}\n";
    }
    
    // Check permissions
    if (is_dir($path) && is_writable($path)) {
        echo "   ✅ Directory is writable\n";
    } elseif (is_dir($path)) {
        echo "   ⚠️  Directory is not writable, fixing permissions...\n";
        chmod($path, 0755);
        echo "   ✅ Permissions fixed\n";
    }
    
    echo "\n";
}

// Clear compiled views if directory exists
if (is_dir($storagePath)) {
    echo "🧹 Clearing compiled views...\n";
    
    $files = glob($storagePath . '/*.php');
    $count = 0;
    
    if ($files) {
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $count++;
            }
        }
    }
    
    echo "✅ Cleared {$count} compiled view files\n\n";
}

// Create .gitignore files
$gitignoreContent = "*\n!.gitignore\n";

foreach ($paths as $name => $path) {
    if (is_dir($path)) {
        $gitignorePath = $path . '/.gitignore';
        if (!file_exists($gitignorePath)) {
            file_put_contents($gitignorePath, $gitignoreContent);
            echo "✅ Created .gitignore in {$name} directory\n";
        }
    }
}

echo "\n✅ Done!\n";
echo "\nNow you can run:\n";
echo "  php artisan config:clear\n";
echo "  php artisan cache:clear\n";
echo "  php artisan view:clear\n";

