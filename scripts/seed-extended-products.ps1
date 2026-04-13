# Script untuk menambahkan produk dummy tambahan
# Windows PowerShell Script

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Seed Extended Products - Medikindo PO" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "Menambahkan 70+ produk dummy tambahan..." -ForegroundColor Yellow
Write-Host ""

# Run the extended product seeder
php artisan db:seed --class=ExtendedProductSeeder

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "  ✓ Seeding Berhasil!" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Produk tambahan telah ditambahkan:" -ForegroundColor White
    Write-Host "  • Obat Jantung & Kardiovaskular (8 items)" -ForegroundColor Gray
    Write-Host "  • Obat Diabetes (6 items)" -ForegroundColor Gray
    Write-Host "  • Obat Saluran Pernapasan (8 items)" -ForegroundColor Gray
    Write-Host "  • Obat Pencernaan (8 items)" -ForegroundColor Gray
    Write-Host "  • Antibiotik Tambahan (6 items)" -ForegroundColor Gray
    Write-Host "  • Obat Mata & Telinga (6 items)" -ForegroundColor Gray
    Write-Host "  • Obat Hormonal & Endokrin (5 items)" -ForegroundColor Gray
    Write-Host "  • Obat Neurologi & Psikiatri (6 items)" -ForegroundColor Gray
    Write-Host "  • Peralatan Laboratorium (7 items)" -ForegroundColor Gray
    Write-Host ""
    Write-Host "Total: 70+ produk baru" -ForegroundColor Cyan
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Red
    Write-Host "  ✗ Seeding Gagal!" -ForegroundColor Red
    Write-Host "========================================" -ForegroundColor Red
    Write-Host ""
    Write-Host "Silakan cek error di atas." -ForegroundColor Yellow
    Write-Host ""
}
