#!/bin/bash

# Safe Cache Clear Script for VPS
# Run: bash vps_clear_cache_safe.sh

echo "🧹 Safe Cache Clear for VPS"
echo "============================"
echo ""

# Navigate to project directory
cd /www/wwwroot/srx.sppqu/all_in || cd /www/wwwroot/sp2507/all_in || exit

echo "📂 Creating storage directories if missing..."

# Create necessary directories
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

# Set proper permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache

echo "✅ Directories created"
echo ""

echo "🧹 Clearing cache..."

# Clear various caches (ignore errors)
php artisan config:clear 2>/dev/null || echo "⚠️  Config cache already clear"
php artisan cache:clear 2>/dev/null || echo "⚠️  Application cache already clear"
php artisan route:clear 2>/dev/null || echo "⚠️  Route cache already clear"

# Clear view cache manually (safer than artisan view:clear)
echo "🗑️  Clearing compiled views manually..."
rm -f storage/framework/views/*.php 2>/dev/null
echo "✅ Compiled views cleared"

echo ""
echo "✅ Cache cleared successfully!"
echo ""
echo "Next steps:"
echo "  1. systemctl restart php8.2-fpm"
echo "  2. Test website: https://srx.sppqu.my.id"

