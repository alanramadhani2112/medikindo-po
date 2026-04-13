#!/bin/bash

# Seed Master Data Script
# Run this to add all master data (Organizations, Suppliers, Products)

echo ""
echo "╔════════════════════════════════════════════════════════╗"
echo "║      SEED MASTER DATA - MEDIKINDO PO SYSTEM            ║"
echo "╚════════════════════════════════════════════════════════╝"
echo ""

echo "This will create:"
echo "  • 8 Organizations (hospitals, clinics, puskesmas)"
echo "  • 12 Suppliers (pharmaceutical companies)"
echo "  • 100+ Products (medicines, medical supplies)"
echo ""

read -p "Continue? (Y/N): " confirm
if [ "$confirm" != "Y" ] && [ "$confirm" != "y" ]; then
    echo "Cancelled."
    exit 1
fi

echo ""
echo "Running MasterDataSeeder..."
echo ""

php artisan db:seed --class=MasterDataSeeder

echo ""
echo "╔════════════════════════════════════════════════════════╗"
echo "║                  SEEDING COMPLETE!                     ║"
echo "╚════════════════════════════════════════════════════════╝"
echo ""

echo "To verify, run:"
echo "  php artisan tinker"
echo ""
echo "Then in tinker:"
echo "  App\\Models\\Organization::count();"
echo "  App\\Models\\Supplier::count();"
echo "  App\\Models\\Product::count();"
echo ""
