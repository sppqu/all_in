#!/bin/bash

# VPS: Quick Setup iPaymu ENV from Database
# This script automatically copies iPaymu credentials from database to .env file

echo "=========================================="
echo "  iPaymu ENV Setup (from Database)"
echo "=========================================="
echo ""

# Run the PHP script
php setup_ipaymu_env.php

# Check exit code
if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Setup completed successfully!"
    echo ""
    echo "Running automatic post-setup tasks..."
    echo ""
    
    # Clear config cache
    echo "📋 Clearing config cache..."
    php artisan config:clear
    
    echo ""
    echo "✅ All done!"
    echo ""
    echo "Next: Restart PHP-FPM"
    echo "  systemctl restart php8.2-fpm"
    echo ""
else
    echo ""
    echo "❌ Setup failed! Please check the error messages above."
    echo ""
fi

