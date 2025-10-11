#!/bin/bash
# Install Per-Widget Lock System Pre-commit Hook
# Run as root: sudo bash INSTALL-HOOK.sh

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🔒 Installing Per-Widget Lock System Pre-commit Hook"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if [ "$EUID" -ne 0 ]; then
    echo "❌ This script must be run as root"
    echo "   Run: sudo bash INSTALL-HOOK.sh"
    exit 1
fi

# Fix git ownership for root
git config --global --add safe.directory /var/www/vhosts/smartesider.no/nytt.smartesider.no

# Copy hook
cp .githooks/pre-commit .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit

echo "✅ Pre-commit hook installed"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🧪 Testing hook..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Test 1: Unlock widget (should allow commit)
echo "Test 1: Unlocking demo-clock widget"
./scripts/unlock-widget.sh demo-clock
echo "<!-- test -->" >> resources/views/widgets/demo-clock.blade.php
git add resources/views/widgets/demo-clock.blade.php

echo ""
echo "Test 2: Attempting commit with unlocked widget (should succeed)"
if git commit -m "Test: unlocked widget" --dry-run > /dev/null 2>&1; then
    echo "✅ Unlocked widget can be committed"
else
    echo "❌ ERROR: Unlocked widget was blocked"
    git reset HEAD resources/views/widgets/demo-clock.blade.php
    git checkout -- resources/views/widgets/demo-clock.blade.php
    exit 1
fi

# Test 2: Lock widget (should block commit)
echo ""
echo "Test 3: Locking demo-clock widget"
./scripts/lock-widget.sh demo-clock "Test lock" > /dev/null 2>&1

echo ""
echo "Test 4: Attempting commit with locked widget (should fail)"
if git commit -m "Test: locked widget" 2>&1 | grep -q "BLOCKED"; then
    echo "✅ Locked widget was blocked (correct!)"
else
    echo "⚠️  WARNING: Locked widget was NOT blocked"
fi

# Cleanup
echo ""
echo "🧹 Cleaning up test..."
git reset HEAD resources/views/widgets/demo-clock.blade.php 2>/dev/null || true
git checkout -- resources/views/widgets/demo-clock.blade.php 2>/dev/null || true

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Installation Complete!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Widget lock system is now active."
echo "Use: ./scripts/widget-status.sh to see status"
echo ""
