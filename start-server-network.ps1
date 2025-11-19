Write-Host "Starting Laravel Development Server on Network..." -ForegroundColor Green
Write-Host ""
Write-Host "Your IP addresses:" -ForegroundColor Yellow
Write-Host "- Main Network: 192.168.15.29" -ForegroundColor Cyan
Write-Host "- VirtualBox: 192.168.99.1" -ForegroundColor Cyan
Write-Host "- VMware VMnet1: 192.168.172.1" -ForegroundColor Cyan
Write-Host "- VMware VMnet8: 192.168.254.1" -ForegroundColor Cyan
Write-Host ""
Write-Host "Server will be accessible from:" -ForegroundColor Yellow
Write-Host "- http://192.168.15.29:8000" -ForegroundColor Green
Write-Host "- http://192.168.99.1:8000" -ForegroundColor Green
Write-Host "- http://192.168.172.1:8000" -ForegroundColor Green
Write-Host "- http://192.168.254.1:8000" -ForegroundColor Green
Write-Host ""
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Red
Write-Host ""

php artisan serve --host=0.0.0.0 --port=8000


