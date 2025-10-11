#!/bin/bash
# Widget Integrity Verification
# Checks all widgets for common issues

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${YELLOW}🔍 Verifying widget integrity...${NC}"
echo ""

ERROR_COUNT=0
WARNING_COUNT=0

for widget in resources/views/widgets/*.blade.php; do
    if [ ! -f "$widget" ]; then
        continue
    fi
    
    widget_name=$(basename "$widget")
    
    # Sjekk 1: Balanserte <div> tags
    open_divs=$(grep -o '<div' "$widget" | wc -l)
    close_divs=$(grep -o '</div>' "$widget" | wc -l)
    
    if [ "$open_divs" -ne "$close_divs" ]; then
        echo -e "${RED}❌ $widget_name: Unbalanced <div> tags${NC}"
        echo "   Opening: $open_divs, Closing: $close_divs"
        ERROR_COUNT=$((ERROR_COUNT + 1))
    fi
    
    # Sjekk 2: Math.random() (JavaScript i PHP-context)
    if grep -q 'Math\.' "$widget"; then
        echo -e "${RED}❌ $widget_name: Contains Math.random()${NC}"
        echo "   Use PHP rand() instead of JavaScript Math.random()"
        grep -n 'Math\.' "$widget" | head -3 | sed 's/^/   Line /'
        ERROR_COUNT=$((ERROR_COUNT + 1))
    fi
    
    # Sjekk 3: Console.log statements (burde være fjernet)
    if grep -q 'console\.log' "$widget"; then
        echo -e "${YELLOW}⚠️  $widget_name: Contains console.log()${NC}"
        echo "   Consider removing debug statements"
        WARNING_COUNT=$((WARNING_COUNT + 1))
    fi
    
    # Sjekk 4: Unclosed Alpine.js components
    if grep -q 'x-data=' "$widget"; then
        # Sjekk at det er tilsvarende Alpine.js script
        if ! grep -q '<script>' "$widget" && ! grep -q 'function.*{' "$widget"; then
            echo -e "${YELLOW}⚠️  $widget_name: x-data without corresponding function${NC}"
            WARNING_COUNT=$((WARNING_COUNT + 1))
        fi
    fi
    
    # Sjekk 5: Blade syntax errors (@{{ vs {{)
    if grep -q '@{{' "$widget"; then
        escaped_count=$(grep -o '@{{' "$widget" | wc -l)
        echo -e "${YELLOW}⚠️  $widget_name: Contains $escaped_count escaped Blade syntax (@{{)${NC}"
        echo "   This may be intentional or an error - verify manually"
        WARNING_COUNT=$((WARNING_COUNT + 1))
    fi
done

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ $ERROR_COUNT -eq 0 ] && [ $WARNING_COUNT -eq 0 ]; then
    echo -e "${GREEN}✅ All widgets passed integrity check${NC}"
    echo "   No errors or warnings found"
    exit 0
elif [ $ERROR_COUNT -eq 0 ]; then
    echo -e "${YELLOW}⚠️  Integrity check passed with warnings${NC}"
    echo "   Errors: $ERROR_COUNT"
    echo "   Warnings: $WARNING_COUNT"
    exit 0
else
    echo -e "${RED}❌ Integrity check failed${NC}"
    echo "   Errors: $ERROR_COUNT"
    echo "   Warnings: $WARNING_COUNT"
    echo ""
    echo "   Fix the errors above before committing changes."
    exit 1
fi
