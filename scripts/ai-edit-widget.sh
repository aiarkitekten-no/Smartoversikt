#!/bin/bash
# AI-Safe Widget Edit Wrapper
# This script ensures AI can ONLY edit the widget it explicitly unlocks
# Usage: ./scripts/ai-edit-widget.sh <widget-name> <action> [reason]

WIDGET_NAME=$1
ACTION=$2
REASON="${3:-AI edit}"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

if [ -z "$WIDGET_NAME" ] || [ -z "$ACTION" ]; then
    echo -e "${RED}Usage: ./scripts/ai-edit-widget.sh <widget-name> <action> [reason]${NC}"
    echo ""
    echo "Actions:"
    echo "  start   - Unlock widget for editing (shows current status)"
    echo "  done    - Lock widget after editing (with reason)"
    echo "  status  - Check if this widget is locked/unlocked"
    echo ""
    echo "Examples:"
    echo "  ./scripts/ai-edit-widget.sh season-tree-lights start"
    echo "  ./scripts/ai-edit-widget.sh season-tree-lights done 'Added Spotify player'"
    echo "  ./scripts/ai-edit-widget.sh season-tree-lights status"
    exit 1
fi

WIDGET_DIR="resources/views/widgets"
WIDGET_FILE="${WIDGET_DIR}/${WIDGET_NAME}.blade.php"
LOCK_FILE="${WIDGET_DIR}/.${WIDGET_NAME}.lock"

# Sjekk om widget-fil eksisterer
if [ ! -f "$WIDGET_FILE" ]; then
    echo -e "${RED}❌ Widget file not found: $WIDGET_FILE${NC}"
    echo ""
    echo "Available widgets:"
    ls -1 ${WIDGET_DIR}/*.blade.php | xargs -n1 basename | sed 's/.blade.php$//' | sed 's/^/  /'
    exit 1
fi

case "$ACTION" in
    start)
        echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
        echo -e "${BLUE}🤖 AI WIDGET EDIT - STARTING${NC}"
        echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
        echo ""
        
        # Vis ALLE widgets status FØRST
        echo -e "${YELLOW}📊 Current protection status:${NC}"
        LOCKED_COUNT=$(ls -1 ${WIDGET_DIR}/.*.lock 2>/dev/null | wc -l)
        TOTAL_COUNT=$(ls -1 ${WIDGET_DIR}/*.blade.php 2>/dev/null | wc -l)
        UNLOCKED_COUNT=$((TOTAL_COUNT - LOCKED_COUNT))
        
        echo "   🔒 Locked widgets: $LOCKED_COUNT"
        echo "   🔓 Unlocked widgets: $UNLOCKED_COUNT"
        echo "   📦 Total widgets: $TOTAL_COUNT"
        echo ""
        
        # Vis andre ulåste widgets (ADVARSEL hvis flere enn 0)
        if [ $UNLOCKED_COUNT -gt 0 ]; then
            echo -e "${RED}⚠️  WARNING: Other unlocked widgets detected!${NC}"
            echo ""
            for widget_file in ${WIDGET_DIR}/*.blade.php; do
                widget=$(basename "$widget_file" .blade.php)
                lock="${WIDGET_DIR}/.${widget}.lock"
                if [ ! -f "$lock" ]; then
                    echo -e "   ${YELLOW}🔓 $widget${NC}"
                fi
            done
            echo ""
            echo -e "${YELLOW}   Recommendation: Lock these before unlocking $WIDGET_NAME${NC}"
            echo -e "${YELLOW}   Run: ./scripts/lock-all-widgets.sh 'Lock before AI edit'${NC}"
            echo ""
            read -p "   Continue anyway? (yes/NO): " confirm
            if [ "$confirm" != "yes" ]; then
                echo "   Cancelled."
                exit 1
            fi
            echo ""
        fi
        
        # Lås opp target widget
        ./scripts/unlock-widget.sh "$WIDGET_NAME"
        
        echo ""
        echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
        echo -e "${GREEN}✅ SAFE TO EDIT: $WIDGET_NAME${NC}"
        echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
        echo ""
        echo "   File: $WIDGET_FILE"
        echo ""
        echo -e "${RED}   ⚠️  CRITICAL: Only edit THIS widget!${NC}"
        echo -e "${RED}   ⚠️  Do NOT touch any other widget files!${NC}"
        echo ""
        echo "   When done, run:"
        echo -e "   ${GREEN}./scripts/ai-edit-widget.sh $WIDGET_NAME done 'What you did'${NC}"
        ;;
        
    done)
        echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
        echo -e "${BLUE}🤖 AI WIDGET EDIT - FINISHING${NC}"
        echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
        echo ""
        
        # Sjekk om widget faktisk er ulåst
        if [ -f "$LOCK_FILE" ]; then
            echo -e "${YELLOW}⚠️  Widget is already locked: $WIDGET_NAME${NC}"
            echo "   No action needed."
            exit 0
        fi
        
        # Lås widget
        ./scripts/lock-widget.sh "$WIDGET_NAME" "$REASON"
        
        echo ""
        echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
        echo -e "${GREEN}✅ WIDGET PROTECTED: $WIDGET_NAME${NC}"
        echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
        
        # Vis final status
        echo ""
        LOCKED_COUNT=$(ls -1 ${WIDGET_DIR}/.*.lock 2>/dev/null | wc -l)
        TOTAL_COUNT=$(ls -1 ${WIDGET_DIR}/*.blade.php 2>/dev/null | wc -l)
        echo -e "${GREEN}   Protection: $LOCKED_COUNT/$TOTAL_COUNT widgets locked${NC}"
        ;;
        
    status)
        echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
        echo -e "${BLUE}📊 WIDGET STATUS: $WIDGET_NAME${NC}"
        echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
        echo ""
        
        if [ -f "$LOCK_FILE" ]; then
            echo -e "${YELLOW}🔒 Status: LOCKED${NC}"
            echo ""
            echo "Lock file info:"
            cat "$LOCK_FILE" | sed 's/^/   /'
            echo ""
            echo "To unlock:"
            echo -e "   ${GREEN}./scripts/ai-edit-widget.sh $WIDGET_NAME start${NC}"
        else
            echo -e "${GREEN}🔓 Status: UNLOCKED${NC}"
            echo ""
            echo "   File: $WIDGET_FILE"
            echo -e "${RED}   ⚠️  This widget can be edited!${NC}"
            echo ""
            echo "To lock:"
            echo -e "   ${GREEN}./scripts/ai-edit-widget.sh $WIDGET_NAME done 'Reason'${NC}"
        fi
        
        echo ""
        echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
        ;;
        
    *)
        echo -e "${RED}❌ Invalid action: $ACTION${NC}"
        echo ""
        echo "Valid actions: start, done, status"
        exit 1
        ;;
esac
