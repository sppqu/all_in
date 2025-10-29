#!/bin/bash

# VPS: Check iPaymu ENV Configuration
# This script checks if iPaymu credentials are properly set in .env file

echo "=========================================="
echo "  iPaymu ENV Configuration Checker"
echo "=========================================="
echo ""

# Check if .env file exists
if [ ! -f .env ]; then
    echo "❌ ERROR: .env file not found!"
    echo "Please create .env file from .env.example"
    exit 1
fi

echo "✅ .env file found"
echo ""

# Check IPAYMU_VA
echo "📋 Checking IPAYMU_VA..."
if grep -q "^IPAYMU_VA=" .env; then
    VA_VALUE=$(grep "^IPAYMU_VA=" .env | cut -d '=' -f2-)
    if [ -z "$VA_VALUE" ] || [ "$VA_VALUE" = "your_va_here" ]; then
        echo "❌ IPAYMU_VA is EMPTY or placeholder!"
        echo "   Current value: $VA_VALUE"
        VA_OK=0
    else
        VA_LENGTH=${#VA_VALUE}
        echo "✅ IPAYMU_VA is SET"
        echo "   Length: $VA_LENGTH characters"
        echo "   Preview: ${VA_VALUE:0:10}...${VA_VALUE: -5}"
        VA_OK=1
    fi
else
    echo "❌ IPAYMU_VA not found in .env!"
    echo "   Add: IPAYMU_VA=your_va_number_here"
    VA_OK=0
fi
echo ""

# Check IPAYMU_API_KEY
echo "📋 Checking IPAYMU_API_KEY..."
if grep -q "^IPAYMU_API_KEY=" .env; then
    API_KEY_VALUE=$(grep "^IPAYMU_API_KEY=" .env | cut -d '=' -f2-)
    if [ -z "$API_KEY_VALUE" ] || [ "$API_KEY_VALUE" = "your_api_key_here" ]; then
        echo "❌ IPAYMU_API_KEY is EMPTY or placeholder!"
        echo "   Current value: $API_KEY_VALUE"
        API_KEY_OK=0
    else
        API_KEY_LENGTH=${#API_KEY_VALUE}
        echo "✅ IPAYMU_API_KEY is SET"
        echo "   Length: $API_KEY_LENGTH characters"
        echo "   Preview: ${API_KEY_VALUE:0:15}...${API_KEY_VALUE: -10}"
        API_KEY_OK=1
    fi
else
    echo "❌ IPAYMU_API_KEY not found in .env!"
    echo "   Add: IPAYMU_API_KEY=your_api_key_here"
    API_KEY_OK=0
fi
echo ""

# Check IPAYMU_SANDBOX
echo "📋 Checking IPAYMU_SANDBOX..."
if grep -q "^IPAYMU_SANDBOX=" .env; then
    SANDBOX_VALUE=$(grep "^IPAYMU_SANDBOX=" .env | cut -d '=' -f2-)
    if [ "$SANDBOX_VALUE" = "true" ]; then
        echo "🧪 Mode: SANDBOX (testing)"
        echo "   API URL: https://sandbox.ipaymu.com"
    else
        echo "🚀 Mode: PRODUCTION"
        echo "   API URL: https://my.ipaymu.com"
    fi
else
    echo "⚠️  IPAYMU_SANDBOX not found in .env (default: true/sandbox)"
    SANDBOX_VALUE="true"
fi
echo ""

# Summary
echo "=========================================="
echo "  Summary"
echo "=========================================="
if [ $VA_OK -eq 1 ] && [ $API_KEY_OK -eq 1 ]; then
    echo "✅ iPaymu ENV Configuration: VALID"
    echo ""
    echo "Config will be used for:"
    echo "  - Addon Purchase"
    echo "  - Subscription Payment"
    echo "  - SPMB Step 2 (QRIS)"
    echo ""
    echo "Next steps:"
    echo "  1. Clear config cache: php artisan config:clear"
    echo "  2. Test with: php test_ipaymu_api.php"
    echo "  3. Restart PHP: systemctl restart php8.2-fpm"
else
    echo "❌ iPaymu ENV Configuration: INVALID"
    echo ""
    echo "Please fix the following in .env file:"
    if [ $VA_OK -eq 0 ]; then
        echo "  ❌ IPAYMU_VA is missing or empty"
    fi
    if [ $API_KEY_OK -eq 0 ]; then
        echo "  ❌ IPAYMU_API_KEY is missing or empty"
    fi
    echo ""
    echo "Get credentials from:"
    if [ "$SANDBOX_VALUE" = "true" ]; then
        echo "  https://sandbox.ipaymu.com → Settings → API Configuration"
    else
        echo "  https://my.ipaymu.com → Settings → API Configuration"
    fi
    echo ""
    echo "Example .env entries:"
    echo "  IPAYMU_VA=1234567890123456"
    echo "  IPAYMU_API_KEY=PROD-ABC123DEF456GHI789JKL..."
    echo "  IPAYMU_SANDBOX=false"
fi
echo ""
echo "=========================================="

