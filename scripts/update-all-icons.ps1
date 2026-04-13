# Update All Icons - Replace with contextual icons

Write-Host "Updating icons in view files..." -ForegroundColor Yellow

$files = Get-ChildItem -Path "resources/views" -Filter "*.blade.php" -Recurse

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    $modified = $false
    
    # Update Edit button icon
    if ($content -match 'ki-pencil') {
        $content = $content -replace 'ki-pencil', 'ki-note-2'
        $modified = $true
    }
    
    if ($modified) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
        Write-Host "Updated: $($file.Name)" -ForegroundColor Green
    }
}

Write-Host "`nAll icons updated!" -ForegroundColor Green
