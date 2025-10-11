#!/bin/bash
# Lock All Widgets - Initial Setup
# Usage: ./scripts/lock-all-widgets.sh [reason]

LOCK_REASON="${1:-Initial protection - AI Safety}"
WIDGET_DIR="resources/views/widgets"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}🔒 Locking all widgets...${NC}"
echo ""

LOCKED_COUNT=0
ALREADY_LOCKED=0
FAILED_COUNT=0

for widget_file in ${WIDGET_DIR}/*.blade.php; do
    if [ ! -f "$widget_file" ]; then
        continue
    fi
    
    widget_name=$(basename "$widget_file" .blade.php)
    lock_file="${WIDGET_DIR}/.${widget_name}.lock"
    
    # Sjekk om allerede låst
    if [ -f "$lock_file" ]; then
        echo -e "${YELLOW}⏭️  $widget_name (already locked)${NC}"
        ALREADY_LOCKED=$((ALREADY_LOCKED + 1))
        continue
    fi
    
    # Opprett lock-fil
    cat > "$lock_file" << EOF
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🔒 WIDGET LOCKED
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Widget:     $widget_name
Locked at:  $(date '+%Y-%m-%d %H:%M:%S')
Locked by:  $(whoami)@$(hostname)
Reason:     $LOCK_REASON

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

⚠️  This widget is LOCKED and cannot be edited.

To unlock, run:
  ./scripts/unlock-widget.sh $widget_name

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
EOF
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ $widget_name${NC}"
        LOCKED_COUNT=$((LOCKED_COUNT + 1))
    else
        echo -e "${RED}❌ $widget_name (failed)${NC}"
        FAILED_COUNT=$((FAILED_COUNT + 1))
    fi
done

echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}🔒 Lock Summary:${NC}"
echo ""
echo "   ✅ Newly locked: $LOCKED_COUNT widgets"
echo "   ⏭️  Already locked: $ALREADY_LOCKED widgets"
if [ $FAILED_COUNT -gt 0 ]; then
    echo -e "   ${RED}❌ Failed: $FAILED_COUNT widgets${NC}"
fi
echo ""
TOTAL_LOCKED=$((LOCKED_COUNT + ALREADY_LOCKED))
echo -e "${GREEN}   Total protected: $TOTAL_LOCKED widgets${NC}"
echo ""
echo "   Reason: $LOCK_REASON"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo "All widgets are now locked and protected."
echo "Use './scripts/unlock-widget.sh <name>' to edit a specific widget."
