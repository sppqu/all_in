#!/bin/bash

echo "================================================"
echo "🔧 VPS Route Fix Script - Cleaning Midtrans/Tripay"
echo "================================================"
echo ""

# Navigate to project directory
cd /var/www/html/sppqu_addon || exit 1

echo "📁 Current directory: $(pwd)"
echo ""

# Step 1: Clear all Laravel caches
echo "🗑️  Step 1: Clearing Laravel caches..."
php artisan config:clear 2>&1
php artisan cache:clear 2>&1
php artisan route:clear 2>&1
php artisan view:clear 2>&1
php artisan optimize:clear 2>&1
echo "✅ Laravel caches cleared"
echo ""

# Step 2: Clear bootstrap cache files
echo "🗑️  Step 2: Clearing bootstrap cache..."
rm -f bootstrap/cache/*.php
rm -f bootstrap/cache/packages.php
rm -f bootstrap/cache/services.php
echo "✅ Bootstrap cache cleared"
echo ""

# Step 3: Clear composer autoload cache
echo "🗑️  Step 3: Clearing composer autoload cache..."
rm -f vendor/composer/autoload_classmap.php
rm -f vendor/composer/autoload_static.php
echo "✅ Composer cache cleared"
echo ""

# Step 4: Regenerate composer autoload
echo "🔄 Step 4: Regenerating composer autoload..."
composer dump-autoload -o --no-interaction 2>&1
echo "✅ Composer autoload regenerated"
echo ""

# Step 5: Cache routes and config (for production)
echo "⚡ Step 5: Caching for production..."
php artisan config:cache 2>&1
php artisan route:cache 2>&1
php artisan view:cache 2>&1
echo "✅ Production caches created"
echo ""

# Step 6: Set proper permissions
echo "🔐 Step 6: Setting permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
echo "✅ Permissions set"
echo ""

# Step 7: Restart PHP-FPM
echo "🔄 Step 7: Restarting PHP-FPM..."
systemctl restart php8.2-fpm 2>&1 || systemctl restart php8.1-fpm 2>&1 || systemctl restart php-fpm 2>&1
echo "✅ PHP-FPM restarted"
echo ""

# Step 8: Test route
echo "🧪 Step 8: Testing cart payment route..."
php artisan route:list --name=cart.payment.ipaymu 2>&1
echo ""

echo "================================================"
echo "✅ VPS Route Fix Completed!"
echo "================================================"
echo ""
echo "📝 Next steps:"
echo "1. Test cart payment di browser: https://srx.sppqu.my.id/student/cart"
echo "2. Cek log jika masih error: tail -f storage/logs/laravel.log"
echo ""

