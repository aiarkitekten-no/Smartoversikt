#!/bin/bash
# Script to setup firewall blocking permissions for SkyDash

# This script should be run as root
if [ "$EUID" -ne 0 ]; then
    echo "Please run as root (sudo)"
    exit 1
fi

# Get the web server user (usually www-data or nginx)
WEB_USER=$(ps aux | grep -E '(apache|nginx|httpd)' | grep -v root | head -1 | awk '{print $1}')

if [ -z "$WEB_USER" ]; then
    echo "Could not detect web server user. Defaulting to www-data"
    WEB_USER="www-data"
fi

echo "Setting up firewall permissions for user: $WEB_USER"

# Create sudoers file for dashboard firewall control
SUDOERS_FILE="/etc/sudoers.d/skydash-firewall"

cat > "$SUDOERS_FILE" << EOF
# SkyDash Dashboard - Firewall Control
# Allow web server to manage iptables for security blocking

# iptables commands for blocking IPs
$WEB_USER ALL=(ALL) NOPASSWD: /sbin/iptables -N DASHBOARD_BLOCKS
$WEB_USER ALL=(ALL) NOPASSWD: /sbin/iptables -C INPUT -j DASHBOARD_BLOCKS
$WEB_USER ALL=(ALL) NOPASSWD: /sbin/iptables -I INPUT * -j DASHBOARD_BLOCKS
$WEB_USER ALL=(ALL) NOPASSWD: /sbin/iptables -I DASHBOARD_BLOCKS * -s * -j DROP
$WEB_USER ALL=(ALL) NOPASSWD: /sbin/iptables -D DASHBOARD_BLOCKS -s * -j DROP

# fail2ban commands for adding IPs to jails
$WEB_USER ALL=(ALL) NOPASSWD: /usr/bin/fail2ban-client set * banip *

# at command for scheduling unblock (minutes/hours)
$WEB_USER ALL=(ALL) NOPASSWD: /usr/bin/at now + * minutes
$WEB_USER ALL=(ALL) NOPASSWD: /usr/bin/at now + * hours
EOF

# Set correct permissions on sudoers file
chmod 0440 "$SUDOERS_FILE"

# Validate sudoers file
visudo -c -f "$SUDOERS_FILE"

if [ $? -eq 0 ]; then
    echo "✅ Sudoers file created successfully: $SUDOERS_FILE"
    echo "✅ User '$WEB_USER' can now manage firewall blocks"
else
    echo "❌ Error in sudoers file! Removing..."
    rm "$SUDOERS_FILE"
    exit 1
fi

# Create the DASHBOARD_BLOCKS chain if it doesn't exist
iptables -N DASHBOARD_BLOCKS 2>/dev/null || echo "DASHBOARD_BLOCKS chain already exists"
iptables -C INPUT -j DASHBOARD_BLOCKS 2>/dev/null || iptables -I INPUT 1 -j DASHBOARD_BLOCKS

echo "✅ Firewall chain DASHBOARD_BLOCKS created"
echo ""
echo "Setup complete! The dashboard can now block IPs via:"
echo "  - iptables (temporary blocks: 30m auto or 2h manual)"
echo "  - fail2ban (persistent blocks)"
