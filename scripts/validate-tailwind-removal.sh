#!/bin/bash
# validate-tailwind-removal.sh
# CSS Validation Script to detect remaining Tailwind classes
# Part of Tailwind to Bootstrap 5 conversion project

echo "=========================================="
echo "Tailwind CSS Class Removal Validator"
echo "=========================================="
echo ""

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Tailwind-specific patterns to detect
declare -a patterns=(
    # Flexbox patterns
    "flex-col"
    "flex-row-reverse"
    "flex-col-reverse"
    "items-start"
    "items-center"
    "items-end"
    "items-baseline"
    "items-stretch"
    "justify-start"
    "justify-center"
    "justify-end"
    "justify-between"
    "justify-around"
    "justify-evenly"
    
    # Display patterns
    "hidden"
    "inline-flex"
    
    # Spacing patterns (Tailwind-specific values)
    "space-x-"
    "space-y-"
    
    # Width/Height patterns with arbitrary values
    "w-\["
    "h-\["
    "min-w-\["
    "max-w-\["
    "min-h-\["
    "max-h-\["
    
    # Color patterns (Tailwind-specific)
    "text-gray-[0-9]"
    "bg-gray-[0-9]"
    "bg-blue-[0-9]"
    "bg-green-[0-9]"
    "bg-red-[0-9]"
    "bg-yellow-[0-9]"
    "text-blue-[0-9]"
    "text-green-[0-9]"
    "text-red-[0-9]"
    "text-yellow-[0-9]"
    "border-gray-[0-9]"
    
    # Responsive prefixes (Tailwind-specific)
    "sm:"
    "md:"
    "lg:"
    "xl:"
    "2xl:"
    
    # State prefixes (Tailwind-specific)
    "hover:"
    "focus:"
    "active:"
    "disabled:"
    "group-hover:"
    
    # Grid patterns
    "grid-cols-"
    "col-span-"
    "gap-x-"
    "gap-y-"
    
    # Border patterns
    "rounded-lg"
    "rounded-md"
    "rounded-sm"
    "rounded-full"
    "divide-y"
    "divide-x"
    
    # Shadow patterns
    "shadow-sm"
    "shadow-md"
    "shadow-lg"
    
    # Typography patterns
    "text-xs"
    "text-sm"
    "text-base"
    "text-lg"
    "text-xl"
    "text-2xl"
    "text-3xl"
    "font-medium"
    "tracking-wider"
    "tracking-wide"
    "leading-tight"
    "leading-snug"
    
    # Other common Tailwind patterns
    "whitespace-nowrap"
    "truncate"
    "overflow-hidden"
    "overflow-auto"
    "overflow-scroll"
)

found=0
total_issues=0
declare -A file_issues

echo "Scanning Blade view files for Tailwind CSS classes..."
echo ""

# Scan each pattern
for pattern in "${patterns[@]}"; do
    # Search in resources/views directory, excluding comments
    results=$(grep -rn "$pattern" resources/views/ --include="*.blade.php" 2>/dev/null | grep -v "{{--" | grep -v "//" | grep -v "<!--")
    
    if [ ! -z "$results" ]; then
        if [ $found -eq 0 ]; then
            echo -e "${RED}✗ Tailwind classes detected!${NC}"
            echo ""
        fi
        
        echo -e "${YELLOW}Pattern found: ${pattern}${NC}"
        echo "$results"
        echo ""
        
        found=1
        
        # Count issues per file
        while IFS= read -r line; do
            file=$(echo "$line" | cut -d':' -f1)
            ((file_issues[$file]++))
            ((total_issues++))
        done <<< "$results"
    fi
done

echo "=========================================="
echo "Validation Summary"
echo "=========================================="

if [ $found -eq 0 ]; then
    echo -e "${GREEN}✓ SUCCESS: No Tailwind classes found!${NC}"
    echo ""
    echo "All views have been successfully converted to Bootstrap 5."
    exit 0
else
    echo -e "${RED}✗ FAILED: Tailwind classes detected${NC}"
    echo ""
    echo "Total issues found: $total_issues"
    echo ""
    echo "Files with issues:"
    for file in "${!file_issues[@]}"; do
        echo "  - $file (${file_issues[$file]} issues)"
    done
    echo ""
    echo "Please review and convert the remaining Tailwind classes to Bootstrap 5 equivalents."
    echo "Refer to BOOTSTRAP_QUICK_REFERENCE.md for class mappings."
    exit 1
fi
