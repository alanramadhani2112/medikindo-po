#!/bin/bash
# Script untuk update harga produk
# Linux/Mac Bash Script

echo "========================================"
echo "  Update Product Prices - Medikindo PO"
echo "========================================"
echo ""

echo "Mengupdate harga produk yang belum ada harga..."
echo ""

# Run the price update seeder
php artisan db:seed --class=UpdateProductPricesSeeder

if [ $? -eq 0 ]; then
    echo ""
    echo "========================================"
    echo "  ✓ Update Berhasil!"
    echo "========================================"
    echo ""
    echo "Harga produk telah diupdate dengan harga realistis"
    echo ""
    
    # Show summary
    echo "Verifikasi hasil:"
    php artisan tinker --execute="echo 'Total Products: ' . App\Models\Product::count() . PHP_EOL; echo 'Products with price > 0: ' . App\Models\Product::where('price', '>', 0)->count() . PHP_EOL; echo 'Average price: Rp ' . number_format(App\Models\Product::avg('price'), 0, ',', '.') . PHP_EOL;"
    
else
    echo ""
    echo "========================================"
    echo "  ✗ Update Gagal!"
    echo "========================================"
    echo ""
    echo "Silakan cek error di atas."
    echo ""
fi
