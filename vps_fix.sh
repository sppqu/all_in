#!/bin/bash

# Script untuk fix dropdown kas di VPS
# Cara pakai: bash vps_fix.sh

echo "=========================================="
echo "  FIX DROPDOWN KAS DI VPS"
echo "=========================================="
echo ""

# Warna untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Clear cache Laravel
echo -e "${YELLOW}1. Clearing Laravel cache...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
echo -e "${GREEN}✓ Cache cleared${NC}"
echo ""

# 2. Jalankan migration (jika belum)
echo -e "${YELLOW}2. Running migrations...${NC}"
php artisan migrate --force
echo -e "${GREEN}✓ Migration completed${NC}"
echo ""

# 3. Cek dan fix data kas
echo -e "${YELLOW}3. Checking kas data...${NC}"
php vps_check_and_fix.php
echo ""

# 4. Optimize
echo -e "${YELLOW}4. Optimizing application...${NC}"
php artisan optimize
echo -e "${GREEN}✓ Optimization completed${NC}"
echo ""

echo "=========================================="
echo -e "${GREEN}✓ SELESAI!${NC}"
echo "=========================================="
echo ""
echo "Langkah selanjutnya:"
echo "1. Buka halaman Pos Penerimaan/Pengeluaran"
echo "2. Hard refresh browser (Ctrl+Shift+R)"
echo "3. Coba tambah transaksi"
echo ""

