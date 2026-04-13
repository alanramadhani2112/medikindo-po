# Seed Products Script (PowerShell)
# Run this to add dummy product data

Write-Host "================================" -ForegroundColor Cyan
Write-Host "  Seeding Product Data" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

# Check if suppliers exist
Write-Host "Checking for suppliers..." -ForegroundColor Yellow
php artisan tinker --execute="echo 'Suppliers: ' . App\Models\Supplier::count();"

Write-Host ""
Write-Host "Running ProductSeeder..." -ForegroundColor Yellow
php artisan db:seed --class=ProductSeeder

Write-Host ""
Write-Host "================================" -ForegroundColor Green
Write-Host "  Seeding Complete!" -ForegroundColor Green
Write-Host "================================" -ForegroundColor Green
Write-Host ""
Write-Host "To verify, run:" -ForegroundColor Cyan
Write-Host "  php artisan tinker --execute=`"echo 'Products: ' . App\Models\Product::count();`"" -ForegroundColor White
