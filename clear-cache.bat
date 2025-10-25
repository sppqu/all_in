@echo off
echo ========================================
echo   CLEAR ALL CACHE - SPPQU
echo ========================================
echo.

echo [1] Clearing View Cache...
php artisan view:clear
echo.

echo [2] Clearing Config Cache...
php artisan config:clear
echo.

echo [3] Clearing Route Cache...
php artisan route:clear
echo.

echo [4] Clearing Application Cache...
php artisan cache:clear
echo.

echo [5] Clearing Compiled Files...
php artisan clear-compiled
echo.

echo ========================================
echo   SEMUA CACHE BERHASIL DIHAPUS!
echo ========================================
echo.
echo Refresh browser Anda (Ctrl+Shift+R)
echo.
pause

