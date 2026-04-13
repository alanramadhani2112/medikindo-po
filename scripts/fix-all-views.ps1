# Fix All Views - Remove Duplicate Containers and Headers

$viewFiles = @(
    "resources/views/approvals/index.blade.php",
    "resources/views/goods-receipts/index.blade.php",
    "resources/views/payments/index.blade.php",
    "resources/views/financial-controls/index.blade.php",
    "resources/views/organizations/index.blade.php",
    "resources/views/suppliers/index.blade.php",
    "resources/views/products/index.blade.php",
    "resources/views/users/index.blade.php",
    "resources/views/invoices/index_customer.blade.php",
    "resources/views/invoices/index_supplier.blade.php",
    "resources/views/notifications/index.blade.php"
)

foreach ($file in $viewFiles) {
    if (Test-Path $file) {
        Write-Host "Processing: $file" -ForegroundColor Yellow
        
        $content = Get-Content $file -Raw
        
        # Remove opening container-fluid
        $content = $content -replace '<div class="container-fluid">\s*\r?\n', ''
        
        # Remove page header section
        $content = $content -replace '(?s)\{\{-- Page Header --\}\}.*?</div>\s*\r?\n\s*\r?\n', ''
        
        # Remove closing container-fluid at the end
        $content = $content -replace '</div>\s*\r?\n@endsection', '@endsection'
        
        # Fix spacing
        $content = $content -replace 'card mb-7', 'card mb-5'
        
        # Fix pagination spacing
        $content = $content -replace 'd-flex justify-content-between align-items-center mt-7', 'd-flex flex-stack flex-wrap pt-7'
        $content = $content -replace 'd-flex justify-content-center mt-7', 'd-flex flex-stack flex-wrap pt-7'
        
        # Save the file
        Set-Content -Path $file -Value $content -NoNewline
        
        Write-Host "Fixed: $file" -ForegroundColor Green
    } else {
        Write-Host "Not found: $file" -ForegroundColor Red
    }
}

Write-Host "`nAll views fixed!" -ForegroundColor Green
