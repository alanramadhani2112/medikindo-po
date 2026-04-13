# Seed Master Data Script (PowerShell)
# Run this to add all master data (Organizations, Suppliers, Products)

Write-Host ""
Write-Host "╔════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║      SEED MASTER DATA - MEDIKINDO PO SYSTEM            ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

Write-Host "This will create:" -ForegroundColor Yellow
Write-Host "  • 8 Organizations (hospitals, clinics, puskesmas)" -ForegroundColor White
Write-Host "  • 12 Suppliers (pharmaceutical companies)" -ForegroundColor White
Write-Host "  • 100+ Products (medicines, medical supplies)" -ForegroundColor White
Write-Host ""

$confirm = Read-Host "Continue? (Y/N)"
if ($confirm -ne "Y" -and $confirm -ne "y") {
    Write-Host "Cancelled." -ForegroundColor Red
    exit
}

Write-Host ""
Write-Host "Running MasterDataSeeder..." -ForegroundColor Yellow
Write-Host ""

php artisan db:seed --class=MasterDataSeeder

Write-Host ""
Write-Host "╔════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║                  SEEDING COMPLETE!                     ║" -ForegroundColor Green
Write-Host "╚════════════════════════════════════════════════════════╝" -ForegroundColor Green
Write-Host ""

Write-Host "To verify, run:" -ForegroundColor Cyan
Write-Host "  php artisan tinker" -ForegroundColor White
Write-Host ""
Write-Host "Then in tinker:" -ForegroundColor Cyan
Write-Host "  App\Models\Organization::count();" -ForegroundColor White
Write-Host "  App\Models\Supplier::count();" -ForegroundColor White
Write-Host "  App\Models\Product::count();" -ForegroundColor White
Write-Host ""
