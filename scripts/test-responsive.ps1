# RESPONSIVE DESIGN TESTING SCRIPT
# Medikindo Procurement System
# Tests responsive CSS and view implementations

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "RESPONSIVE DESIGN TESTING" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$errors = 0
$warnings = 0
$passed = 0

# Test 1: Check if custom-layout.css exists
Write-Host "[TEST 1] Checking custom-layout.css..." -ForegroundColor Yellow
if (Test-Path "public/css/custom-layout.css") {
    Write-Host "  [OK] File exists" -ForegroundColor Green
    $passed++
    
    # Check for responsive breakpoints (simple string match)
    $css = Get-Content "public/css/custom-layout.css" -Raw
    
    $breakpoints = @(
        "min-width: 1400px",
        "max-width: 1399px",
        "max-width: 991px",
        "max-width: 767px",
        "max-width: 575px",
        "orientation: landscape",
        "@media print",
        "prefers-contrast: high",
        "prefers-reduced-motion: reduce"
    )
    
    foreach ($breakpoint in $breakpoints) {
        if ($css -like "*$breakpoint*") {
            Write-Host "  [OK] Breakpoint found: $breakpoint" -ForegroundColor Green
            $passed++
        }
        else {
            Write-Host "  [FAIL] Missing breakpoint: $breakpoint" -ForegroundColor Red
            $errors++
        }
    }
}
else {
    Write-Host "  [FAIL] File not found" -ForegroundColor Red
    $errors++
}

Write-Host ""

# Test 2: Check responsive classes in views
Write-Host "[TEST 2] Checking responsive classes in views..." -ForegroundColor Yellow

$views = @(
    "resources/views/users/index.blade.php",
    "resources/views/products/index.blade.php"
)

foreach ($view in $views) {
    if (Test-Path $view) {
        Write-Host "  Checking: $view" -ForegroundColor Cyan
        $content = Get-Content $view -Raw
        
        # Check for responsive classes
        $responsiveClasses = @(
            "d-none d-md-table-cell",
            "d-none d-lg-table-cell",
            "d-none d-sm-table-cell"
        )
        
        $foundClasses = 0
        foreach ($class in $responsiveClasses) {
            if ($content -like "*$class*") {
                $foundClasses++
            }
        }
        
        if ($foundClasses -gt 0) {
            Write-Host "    [OK] Found $foundClasses responsive classes" -ForegroundColor Green
            $passed++
        }
        else {
            Write-Host "    [WARN] No responsive classes found" -ForegroundColor Yellow
            $warnings++
        }
    }
    else {
        Write-Host "    [FAIL] File not found: $view" -ForegroundColor Red
        $errors++
    }
}

Write-Host ""

# Test 3: Check CSS structure
Write-Host "[TEST 3] Checking CSS structure..." -ForegroundColor Yellow

if (Test-Path "public/css/custom-layout.css") {
    $css = Get-Content "public/css/custom-layout.css" -Raw
    
    # Check for key responsive rules
    $rules = @{
        "Mobile header height"  = "height: 60px"
        "Tablet header height"  = "height: 70px"
        "Touch optimization"    = "hover: none"
        "Table responsive"      = ".table-responsive"
        "Mobile padding"        = "padding: 0 1rem"
        "Compact cards"         = "padding: 1rem"
    }
    
    foreach ($rule in $rules.GetEnumerator()) {
        if ($css -like "*$($rule.Value)*") {
            Write-Host "  [OK] $($rule.Key)" -ForegroundColor Green
            $passed++
        }
        else {
            Write-Host "  [WARN] Missing: $($rule.Key)" -ForegroundColor Yellow
            $warnings++
        }
    }
}
else {
    Write-Host "  [FAIL] CSS file not found" -ForegroundColor Red
    $errors++
}

Write-Host ""

# Test 4: Check layout files
Write-Host "[TEST 4] Checking layout files..." -ForegroundColor Yellow

$layouts = @(
    "resources/views/layouts/app.blade.php",
    "resources/views/components/layout.blade.php"
)

foreach ($layout in $layouts) {
    if (Test-Path $layout) {
        $content = Get-Content $layout -Raw
        
        # Check for viewport meta tag
        if ($content -like '*name="viewport"*') {
            Write-Host "  [OK] Viewport meta tag found in $layout" -ForegroundColor Green
            $passed++
        }
        else {
            Write-Host "  [FAIL] Missing viewport meta tag in $layout" -ForegroundColor Red
            $errors++
        }
        
        # Check for custom-layout.css link
        if ($content -like '*custom-layout.css*') {
            Write-Host "  [OK] Custom CSS linked in $layout" -ForegroundColor Green
            $passed++
        }
        else {
            Write-Host "  [FAIL] Custom CSS not linked in $layout" -ForegroundColor Red
            $errors++
        }
    }
    else {
        Write-Host "  [FAIL] Layout not found: $layout" -ForegroundColor Red
        $errors++
    }
}

Write-Host ""

# Test 5: Check for common responsive issues
Write-Host "[TEST 5] Checking for common issues..." -ForegroundColor Yellow

if (Test-Path "public/css/custom-layout.css") {
    $css = Get-Content "public/css/custom-layout.css" -Raw
    
    # Check for !important overuse (should be minimal)
    $importantMatches = Select-String -Path "public/css/custom-layout.css" -Pattern "!important" -AllMatches
    $importantCount = ($importantMatches.Matches | Measure-Object).Count
    
    if ($importantCount -lt 50) {
        Write-Host "  [OK] Reasonable use of !important: $importantCount occurrences" -ForegroundColor Green
        $passed++
    }
    else {
        Write-Host "  [WARN] High use of !important: $importantCount occurrences" -ForegroundColor Yellow
        $warnings++
    }
    
    # Check for overflow handling
    if ($css -like "*overflow-x: auto*" -or $css -like "*overflow: auto*") {
        Write-Host "  [OK] Overflow handling implemented" -ForegroundColor Green
        $passed++
    }
    else {
        Write-Host "  [WARN] Consider adding overflow handling" -ForegroundColor Yellow
        $warnings++
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "TEST RESULTS" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Passed:   $passed" -ForegroundColor Green
Write-Host "Warnings: $warnings" -ForegroundColor Yellow
Write-Host "Errors:   $errors" -ForegroundColor Red
Write-Host ""

if ($errors -eq 0 -and $warnings -eq 0) {
    Write-Host "[SUCCESS] ALL TESTS PASSED!" -ForegroundColor Green
    Write-Host "Responsive design is properly implemented." -ForegroundColor Green
    exit 0
}
elseif ($errors -eq 0) {
    Write-Host "[WARNING] TESTS PASSED WITH WARNINGS" -ForegroundColor Yellow
    Write-Host "Responsive design is functional but has minor issues." -ForegroundColor Yellow
    exit 0
}
else {
    Write-Host "[FAILED] TESTS FAILED" -ForegroundColor Red
    Write-Host "Please fix the errors above." -ForegroundColor Red
    exit 1
}
