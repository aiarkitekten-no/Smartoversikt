#!/bin/bash
# Lock Widget After Editing - Per-Widget Lock System
# Usage: ./scripts/lock-widget.sh <widget-name> [reason]

WIDGET_NAME=$1
LOCK_REASON="${2:-Manual lock}"
WIDGET_DIR="resources/views/widgets"
WIDGET_FILE="${WIDGET_DIR}/${WIDGET_NAME}.blade.php"
LOCK_FILE="${WIDGET_DIR}/.${WIDGET_NAME}.lock"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

if [ -z "$WIDGET_NAME" ]; then
    echo -e "${RED}Usage: ./scripts/lock-widget.sh <widget-name> [reason]${NC}"
    echo ""
    echo "Examples:"
    echo "  ./scripts/lock-widget.sh season-tree-lights"
    echo "  ./scripts/lock-widget.sh dev-github 'Finished feature X'"
    echo ""
    echo "Currently unlocked widgets:"
    for widget in ${WIDGET_DIR}/*.blade.php; do
        widget_name=$(basename "$widget" .blade.php)
        lock="${WIDGET_DIR}/.${widget_name}.lock"
        if [ ! -f "$lock" ]; then
            echo -e "  ${GREEN}🔓 $widget_name${NC}"
        fi
    done
    exit 1
fi

# Sjekk om widget-filen eksisterer
if [ ! -f "$WIDGET_FILE" ]; then
    echo -e "${RED}❌ Widget file not found: $WIDGET_FILE${NC}"
    exit 1
fi

# Sjekk om widget allerede er låst
if [ -f "$LOCK_FILE" ]; then
    echo -e "${YELLOW}⚠️  Widget is already locked: $WIDGET_NAME${NC}"
    echo ""
    echo "Lock file contents:"
    cat "$LOCK_FILE" | sed 's/^/   /'
    exit 0
fi

# Kjør integritetsjekk først
if [ -f "scripts/verify-widget-integrity.sh" ]; then
    echo -e "${YELLOW}🔍 Running integrity check before locking...${NC}"
    
    # Midlertidig sjekk kun denne widgeten
    TEMP_CHECK=$(mktemp)
    cat > "$TEMP_CHECK" << 'CHECKSCRIPT'
#!/bin/bash
WIDGET_FILE=$1
widget_name=$(basename "$WIDGET_FILE")
ERROR_COUNT=0

# Sjekk 1: Balanserte <div> tags
open_divs=$(grep -o '<div' "$WIDGET_FILE" | wc -l)
close_divs=$(grep -o '</div>' "$WIDGET_FILE" | wc -l)
if [ "$open_divs" -ne "$close_divs" ]; then
    echo "❌ $widget_name: Unbalanced <div> tags (Opening: $open_divs, Closing: $close_divs)"
    ERROR_COUNT=$((ERROR_COUNT + 1))
fi

# Sjekk 2: Math.random() i feil context
if grep -q 'Math\.' "$WIDGET_FILE"; then
    # Sjekk om det er i :style eller :class bindings (server-side Blade)
    if grep -q ':style.*Math\.' "$WIDGET_FILE" || grep -q ':class.*Math\.' "$WIDGET_FILE"; then
        echo "❌ $widget_name: Contains Math.random() in Blade binding"
        ERROR_COUNT=$((ERROR_COUNT + 1))
    fi
fi

exit $ERROR_COUNT
CHECKSCRIPT
    
    chmod +x "$TEMP_CHECK"
    if ! bash "$TEMP_CHECK" "$WIDGET_FILE"; then
        rm "$TEMP_CHECK"
        echo -e "${RED}❌ Cannot lock widget - integrity check failed${NC}"
        echo "   Fix the errors above before locking."
        exit 1
    fi
    rm "$TEMP_CHECK"
    echo -e "${GREEN}✅ Integrity check passed${NC}"
    echo ""
fi

# Opprett lock-fil med metadata
cat > "$LOCK_FILE" << EOF
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🔒 WIDGET LOCKED
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Widget:     $WIDGET_NAME
Locked at:  $(date '+%Y-%m-%d %H:%M:%S')
Locked by:  $(whoami)@$(hostname)
Reason:     $LOCK_REASON

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

⚠️  This widget is LOCKED and cannot be edited.

To unlock, run:
  ./scripts/unlock-widget.sh $WIDGET_NAME

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
EOF

echo -e "${GREEN}🔒 Widget locked: $WIDGET_NAME${NC}"
echo ""
echo "   Lock file created: $LOCK_FILE"
echo "   Reason: $LOCK_REASON"
echo ""
echo -e "${BLUE}   Widget is now protected from accidental changes.${NC}"
echo ""

# Vis statistikk
LOCKED_COUNT=$(ls -1 ${WIDGET_DIR}/.*.lock 2>/dev/null | wc -l)
TOTAL_COUNT=$(ls -1 ${WIDGET_DIR}/*.blade.php 2>/dev/null | wc -l)
UNLOCKED_COUNT=$((TOTAL_COUNT - LOCKED_COUNT))

echo -e "${BLUE}   Protection status:${NC}"
echo "   🔒 Locked: $LOCKED_COUNT widgets"
echo "   🔓 Unlocked: $UNLOCKED_COUNT widgets"
echo "   📊 Total: $TOTAL_COUNT widgets"
