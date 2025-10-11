#!/bin/bash
# Widget Status - Show lock status of all widgets
# Usage: ./scripts/widget-status.sh

WIDGET_DIR="resources/views/widgets"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}           WIDGET PROTECTION STATUS${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

LOCKED_WIDGETS=()
UNLOCKED_WIDGETS=()

for widget_file in ${WIDGET_DIR}/*.blade.php; do
    if [ ! -f "$widget_file" ]; then
        continue
    fi
    
    widget_name=$(basename "$widget_file" .blade.php)
    lock_file="${WIDGET_DIR}/.${widget_name}.lock"
    
    if [ -f "$lock_file" ]; then
        LOCKED_WIDGETS+=("$widget_name")
    else
        UNLOCKED_WIDGETS+=("$widget_name")
    fi
done

# Vis ulåste widgets først (FARLIG!)
if [ ${#UNLOCKED_WIDGETS[@]} -gt 0 ]; then
    echo -e "${RED}⚠️  UNLOCKED WIDGETS (Can be edited):${NC}"
    echo ""
    for widget in "${UNLOCKED_WIDGETS[@]}"; do
        echo -e "   ${GREEN}🔓 $widget${NC}"
        echo "      File: ${WIDGET_DIR}/${widget}.blade.php"
        echo "      Status: EDITABLE"
        echo ""
    done
    echo -e "${RED}   ⚠️  These widgets are NOT protected!${NC}"
    echo "   To lock: ./scripts/lock-widget.sh <widget-name>"
    echo ""
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
fi

# Vis låste widgets
if [ ${#LOCKED_WIDGETS[@]} -gt 0 ]; then
    echo -e "${GREEN}🔒 LOCKED WIDGETS (Protected):${NC}"
    echo ""
    for widget in "${LOCKED_WIDGETS[@]}"; do
        lock_file="${WIDGET_DIR}/.${widget}.lock"
        
        # Les locked_at fra lock-filen
        locked_at=$(grep "Locked at:" "$lock_file" | cut -d: -f2- | xargs)
        locked_by=$(grep "Locked by:" "$lock_file" | cut -d: -f2- | xargs)
        
        echo -e "   ${YELLOW}🔒 $widget${NC}"
        if [ -n "$locked_at" ]; then
            echo "      Locked: $locked_at"
        fi
        if [ -n "$locked_by" ]; then
            echo "      By: $locked_by"
        fi
        echo "      To unlock: ./scripts/unlock-widget.sh $widget"
        echo ""
    done
fi

# Statistikk
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}📊 Summary:${NC}"
echo ""
echo "   🔒 Locked widgets: ${#LOCKED_WIDGETS[@]}"
echo "   🔓 Unlocked widgets: ${#UNLOCKED_WIDGETS[@]}"
TOTAL=$((${#LOCKED_WIDGETS[@]} + ${#UNLOCKED_WIDGETS[@]}))
echo "   📦 Total widgets: $TOTAL"
echo ""

# Beregn beskyttelsesprosent
if [ $TOTAL -gt 0 ]; then
    PROTECTION_PCT=$(( ${#LOCKED_WIDGETS[@]} * 100 / TOTAL ))
    if [ $PROTECTION_PCT -eq 100 ]; then
        echo -e "   ${GREEN}✅ Protection: ${PROTECTION_PCT}% (Excellent!)${NC}"
    elif [ $PROTECTION_PCT -ge 90 ]; then
        echo -e "   ${GREEN}✅ Protection: ${PROTECTION_PCT}% (Good)${NC}"
    elif [ $PROTECTION_PCT -ge 75 ]; then
        echo -e "   ${YELLOW}⚠️  Protection: ${PROTECTION_PCT}% (Fair)${NC}"
    else
        echo -e "   ${RED}❌ Protection: ${PROTECTION_PCT}% (Poor - Lock more widgets!)${NC}"
    fi
fi

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
