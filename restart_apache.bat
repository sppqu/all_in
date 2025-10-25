@echo off
echo ========================================
echo RESTARTING APACHE FOR TRIPAY FIX
echo ========================================

echo.
echo Stopping Apache...
taskkill /F /IM httpd.exe 2>nul

echo.
echo Waiting 3 seconds...
timeout /t 3 /nobreak >nul

echo.
echo Starting Apache via Laragon...
start "" "C:\laragon\laragon.exe"

echo.
echo ========================================
echo DONE! Please wait 5 seconds for Apache to start
echo Then test: https://c364e925eb21.ngrok-free.app/manage/tripay/callback
echo ========================================
pause


