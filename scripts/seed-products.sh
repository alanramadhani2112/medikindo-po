#!/bin/bash

# Seed Products Script
# Run this to add dummy product data

echo "================================"
echo "  Seeding Product Data"
echo "================================"
echo ""

# Check if suppliers exist
echo "Checking for suppliers..."
php artisan tinker --execute="echo 'Suppliers: ' . App\Models\Supplier::count();"

echo ""
echo "Running ProductSeeder..."
php artisan db:seed --class=ProductSeeder

echo ""
echo "================================"
echo "  Seeding Complete!"
echo "================================"
echo ""
echo "To verify, run:"
echo "  php artisan tinker --execute=\"echo 'Products: ' . App\\Models\\Product::count();\""
