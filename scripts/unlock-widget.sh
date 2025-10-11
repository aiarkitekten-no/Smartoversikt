#!/bin/bash
# Unlock Widget for Editing - Per-Widget Lock System
# Usage: ./scripts/unlock-widget.sh <widget-name>

WIDGET_NAME=$1
WIDGET_DIR="resources/views/widgets"
WIDGET_FILE="${WIDGET_DIR}/${WIDGET_NAME}.blade.php"
LOCK_FILE="${WIDGET_DIR}/.${WIDGET_NAME}.lock"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

if [ -z "$WIDGET_NAME" ]; then
    echo -e "${RED}Usage: ./scripts/unlock-widget.sh <widget-name>${NC}"
    echo ""
    echo "Examples:"
    echo "  ./scripts/unlock-widget.sh season-tree-lights"
    echo "  ./scripts/unlock-widget.sh dev-github"
    echo "  ./scripts/unlock-widget.sh mail-imap-inbox"
    echo ""
    echo "Available locked widgets:"
    for lock in ${WIDGET_DIR}/.*.lock; do
        if [ -f "$lock" ]; then
            widget=$(basename "$lock" .lock | sed 's/^\.//')
            echo -e "  ${YELLOW}🔒 $widget${NC}"
        fi
    done
    exit 1
fi

# Sjekk om widget-filen eksisterer
if [ ! -f "$WIDGET_FILE" ]; then
    echo -e "${RED}❌ Widget file not found: $WIDGET_FILE${NC}"
    echo ""
    echo "Available widgets:"
    ls -1 ${WIDGET_DIR}/*.blade.php | xargs -n1 basename | sed 's/.blade.php$//' | sed 's/^/  /'
    exit 1
fi

# Sjekk om widget allerede er ulåst
if [ ! -f "$LOCK_FILE" ]; then
    echo -e "${YELLOW}⚠️  Widget is already unlocked: $WIDGET_NAME${NC}"
    echo -e "   File: ${BLUE}$WIDGET_FILE${NC}"
    exit 0
fi

# Les lock-info
if [ -f "$LOCK_FILE" ]; then
    echo -e "${BLUE}📋 Lock file info:${NC}"
    cat "$LOCK_FILE" | sed 's/^/   /'
    echo ""
fi

# Slett lock-filen for å låse opp
rm "$LOCK_FILE"

echo -e "${GREEN}🔓 Widget unlocked: $WIDGET_NAME${NC}"
echo ""
echo "   You can now safely edit:"
echo -e "   ${YELLOW}$WIDGET_FILE${NC}"
echo ""
echo -e "${RED}   ⚠️  IMPORTANT: Only this widget is unlocked!${NC}"
echo -e "${RED}   ⚠️  All other widgets remain locked and protected.${NC}"
echo ""
echo "   When done editing, lock it again:"
echo -e "   ${GREEN}./scripts/lock-widget.sh $WIDGET_NAME${NC}"
echo ""

# Vis andre låste widgets
LOCKED_COUNT=$(ls -1 ${WIDGET_DIR}/.*.lock 2>/dev/null | wc -l)
if [ "$LOCKED_COUNT" -gt 0 ]; then
    echo -e "${BLUE}   Other locked widgets: $LOCKED_COUNT${NC}"
fi
