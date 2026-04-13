# Script untuk update harga produk
# Windows PowerShell Script

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Update Product Prices - Medikindo PO" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "Mengupdate harga produk yang belum ada harga..." -ForegroundColor Yellow
Write-Host ""

# Run the price update seeder
php artisan db:seed --class=UpdateProductPricesSeeder

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "  ✓ Update Berhasil!" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Harga produk telah diupdate dengan harga realistis" -ForegroundColor White
    Write-Host ""
    
    # Show summary
    Write-Host "Verifikasi hasil:" -ForegroundColor Cyan
    php artisan tinker --execute="echo 'Total Products: ' . App\Models\Product::count() . PHP_EOL; echo 'Products with price > 0: ' . App\Models\Product::where('price', '>', 0)->count() . PHP_EOL; echo 'Average price: Rp ' . number_format(App\Models\Product::avg('price'), 0, ',', '.') . PHP_EOL;"
    
} else {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Red
    Write-Host "  ✗ Update Gagal!" -ForegroundColor Red
    Write-Host "========================================" -ForegroundColor Red
    Write-Host ""
    Write-Host "Silakan cek error di atas." -ForegroundColor Yellow
    Write-Host ""
}
