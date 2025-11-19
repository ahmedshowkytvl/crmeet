@echo off
echo Starting Laravel Development Server on Network...
echo.
echo Your IP addresses:
echo - Main Network: 192.168.15.29
echo - VirtualBox: 192.168.99.1
echo - VMware VMnet1: 192.168.172.1
echo - VMware VMnet8: 192.168.254.1
echo.
echo Server will be accessible from:
echo - http://192.168.15.29:8000
echo - http://192.168.99.1:8000
echo - http://192.168.172.1:8000
echo - http://192.168.254.1:8000
echo.
echo Press Ctrl+C to stop the server
echo.
php artisan serve --host=0.0.0.0 --port=8000