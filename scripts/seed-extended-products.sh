#!/bin/bash
# Script untuk menambahkan produk dummy tambahan
# Linux/Mac Bash Script

echo "========================================"
echo "  Seed Extended Products - Medikindo PO"
echo "========================================"
echo ""

echo "Menambahkan 70+ produk dummy tambahan..."
echo ""

# Run the extended product seeder
php artisan db:seed --class=ExtendedProductSeeder

if [ $? -eq 0 ]; then
    echo ""
    echo "========================================"
    echo "  ✓ Seeding Berhasil!"
    echo "========================================"
    echo ""
    echo "Produk tambahan telah ditambahkan:"
    echo "  • Obat Jantung & Kardiovaskular (8 items)"
    echo "  • Obat Diabetes (6 items)"
    echo "  • Obat Saluran Pernapasan (8 items)"
    echo "  • Obat Pencernaan (8 items)"
    echo "  • Antibiotik Tambahan (6 items)"
    echo "  • Obat Mata & Telinga (6 items)"
    echo "  • Obat Hormonal & Endokrin (5 items)"
    echo "  • Obat Neurologi & Psikiatri (6 items)"
    echo "  • Peralatan Laboratorium (7 items)"
    echo ""
    echo "Total: 70+ produk baru"
    echo ""
else
    echo ""
    echo "========================================"
    echo "  ✗ Seeding Gagal!"
    echo "========================================"
    echo ""
    echo "Silakan cek error di atas."
    echo ""
fi
