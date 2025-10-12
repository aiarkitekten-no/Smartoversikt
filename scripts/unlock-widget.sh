#!/bin/bash
# Unlock Widget for Editing - Per-Widget Lock System
# Usage: ./scripts/unlock-widget.sh <widget-name>

WIDGET_NAME=$1
WIDGET_DIR="resources/views/widgets"
WIDGET_FILE="${WIDGET_DIR}/${WIDGET_NAME}.blade.php"
LOCK_FILE="${WIDGET_DIR}/.${WIDGET_NAME}.lock"

# Optional admin password protection
ADMIN_HASH_FILE="/etc/smartoversikt/widget_admin.hash"
REQUIRE_INTERACTIVE=true

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

# If an admin password hash is configured, require password before unlocking
if [ -f "$ADMIN_HASH_FILE" ]; then
    if [ "$REQUIRE_INTERACTIVE" = true ] && [ ! -t 0 ]; then
        echo -e "${RED}❌ Interactive terminal required to enter admin password.${NC}"
        echo -e "   Run this command manually in a shell."
        exit 1
    fi

    if ! command -v sha256sum >/dev/null 2>&1 && ! command -v shasum >/dev/null 2>&1; then
        echo -e "${RED}❌ Missing sha256 utility (sha256sum or shasum).${NC}"
        echo "   Install coreutils or ensure shasum is available."
        exit 1
    fi

    # Read stored hash
    STORED_HASH=$(cat "$ADMIN_HASH_FILE" 2>/dev/null | tr -d '\n' | tr -d '\r')
    if [ -z "$STORED_HASH" ]; then
        echo -e "${RED}❌ Admin hash file is empty or unreadable: $ADMIN_HASH_FILE${NC}"
        exit 1
    fi

    # Prompt for password (no echo)
    echo -n "Admin password: "
    stty -echo
    read -r ENTERED_PASS
    stty echo
    echo

    # Compute SHA-256 hash of entered password
    if command -v sha256sum >/dev/null 2>&1; then
        ENTERED_HASH=$(printf '%s' "$ENTERED_PASS" | sha256sum | awk '{print $1}')
    else
        ENTERED_HASH=$(printf '%s' "$ENTERED_PASS" | shasum -a 256 | awk '{print $1}')
    fi
    unset ENTERED_PASS

    # Compare hashes
    if [ "$ENTERED_HASH" != "$STORED_HASH" ]; then
        echo -e "${RED}❌ Invalid admin password. Unlock denied.${NC}"
        exit 1
    fi
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
