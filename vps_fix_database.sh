#!/bin/bash

# VPS Database Connection Fix Script
# Run this on VPS server

echo "🔧 Fixing Database Connection..."
echo ""

# Navigate to project directory
cd /www/wwwroot/srx.sppqu/all_in || exit

# Backup current .env
if [ -f .env ]; then
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
    echo "✅ Backed up .env file"
fi

# Get database credentials
echo "📝 Please enter your database credentials:"
echo ""
read -p "Database Name: " DB_NAME
read -p "Database Username: " DB_USER
read -sp "Database Password: " DB_PASS
echo ""

# Update .env file
echo "📝 Updating .env file..."

# Update database settings
sed -i "s/^DB_CONNECTION=.*/DB_CONNECTION=mysql/" .env
sed -i "s/^DB_HOST=.*/DB_HOST=127.0.0.1/" .env
sed -i "s/^DB_PORT=.*/DB_PORT=3306/" .env
sed -i "s/^DB_DATABASE=.*/DB_DATABASE=${DB_NAME}/" .env
sed -i "s/^DB_USERNAME=.*/DB_USERNAME=${DB_USER}/" .env
sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASS}/" .env

echo "✅ Database configuration updated"
echo ""

# Clear config cache
echo "🧹 Clearing configuration cache..."
php artisan config:clear
php artisan cache:clear

echo "✅ Cache cleared"
echo ""

# Test database connection
echo "🔍 Testing database connection..."
php artisan db:show 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✅ Database connection successful!"
    echo ""
    
    # Ask if user wants to run migration
    read -p "Run migrations now? (y/n): " RUN_MIGRATE
    
    if [ "$RUN_MIGRATE" = "y" ]; then
        echo "🚀 Running migrations..."
        php artisan migrate --force
        echo "✅ Migrations completed!"
    fi
else
    echo "❌ Database connection failed!"
    echo "Please check your credentials and try again."
fi

echo ""
echo "✅ Done!"

