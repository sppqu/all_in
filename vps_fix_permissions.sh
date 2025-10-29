#!/bin/bash

# Script untuk fix permissions di VPS
# Upload ke /www/wwwroot/srx.sppqu/all_in/ dan jalankan: bash vps_fix_permissions.sh

echo "================================================"
echo "   FIXING LARAVEL PERMISSIONS ON VPS"
echo "================================================"
echo ""

# Get current directory
APP_DIR="/www/wwwroot/srx.sppqu/all_in"

# Check if we're in the right directory
if [ ! -f "$APP_DIR/artisan" ]; then
    echo "❌ Error: artisan file not found!"
    echo "   Make sure you're in Laravel root directory"
    exit 1
fi

echo "📂 Working directory: $APP_DIR"
echo ""

# Get web server user (usually www-data or nginx)
WEB_USER=$(ps aux | grep -E 'apache2|httpd|nginx' | grep -v grep | head -1 | awk '{print $1}')
if [ -z "$WEB_USER" ]; then
    WEB_USER="www-data"  # Default fallback
fi

echo "👤 Web server user detected: $WEB_USER"
echo ""

# Fix ownership
echo "1️⃣  Setting ownership to $WEB_USER..."
chown -R $WEB_USER:$WEB_USER $APP_DIR/storage
chown -R $WEB_USER:$WEB_USER $APP_DIR/bootstrap/cache
echo "   ✅ Ownership updated"
echo ""

# Fix permissions for directories
echo "2️⃣  Setting directory permissions (775)..."
find $APP_DIR/storage -type d -exec chmod 775 {} \;
find $APP_DIR/bootstrap/cache -type d -exec chmod 775 {} \;
echo "   ✅ Directory permissions updated"
echo ""

# Fix permissions for files
echo "3️⃣  Setting file permissions (664)..."
find $APP_DIR/storage -type f -exec chmod 664 {} \;
find $APP_DIR/bootstrap/cache -type f -exec chmod 664 {} \;
echo "   ✅ File permissions updated"
echo ""

# Clear compiled views
echo "4️⃣  Clearing compiled views..."
rm -rf $APP_DIR/storage/framework/views/*
echo "   ✅ Compiled views cleared"
echo ""

# Clear cache
echo "5️⃣  Clearing Laravel caches..."
cd $APP_DIR
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
echo "   ✅ Caches cleared"
echo ""

# Verify permissions
echo "6️⃣  Verifying permissions..."
echo ""
echo "   storage/framework/views:"
ls -la $APP_DIR/storage/framework/views | head -5
echo ""
echo "   bootstrap/cache:"
ls -la $APP_DIR/bootstrap/cache | head -5
echo ""

echo "================================================"
echo "   ✅ PERMISSIONS FIXED SUCCESSFULLY!"
echo "================================================"
echo ""
echo "Try accessing your application now."
echo ""

