#!/bin/bash
# Fail2ban Status Wrapper
# Safe read-only access to fail2ban for widget
# Location: /usr/local/bin/fail2ban-status.sh
# Permissions: chmod +x /usr/local/bin/fail2ban-status.sh
# Sudoers: psaadm ALL=(ALL) NOPASSWD: /usr/local/bin/fail2ban-status.sh *

ACTION="$1"
JAIL="$2"

case "$ACTION" in
    status)
        # Get general fail2ban status
        fail2ban-client status 2>/dev/null
        ;;
        
    jail-list)
        # Get list of active jails
        fail2ban-client status 2>/dev/null | grep "Jail list:" | sed 's/.*Jail list:\s*//' | tr ',' '\n' | sed 's/^\s*//'
        ;;
        
    jail-status)
        # Get status for specific jail
        if [ -z "$JAIL" ]; then
            echo "ERROR: Jail name required"
            exit 1
        fi
        fail2ban-client status "$JAIL" 2>/dev/null
        ;;
        
    all-jails)
        # Get status for all jails
        fail2ban-client status 2>/dev/null | grep "Jail list:" | sed 's/.*Jail list:\s*//' | tr ',' '\n' | sed 's/^\s*//' | while read jail; do
            if [ -n "$jail" ]; then
                echo "=== Jail: $jail ==="
                fail2ban-client status "$jail" 2>/dev/null
                echo ""
            fi
        done
        ;;
        
    banned-ips)
        # Get all currently banned IPs across all jails
        echo "=== Currently Banned IPs ==="
        fail2ban-client status 2>/dev/null | grep "Jail list:" | sed 's/.*Jail list:\s*//' | tr ',' '\n' | sed 's/^\s*//' | while read jail; do
            if [ -n "$jail" ]; then
                BANNED=$(fail2ban-client status "$jail" 2>/dev/null | grep "Currently banned" | awk '{print $4}')
                if [ "$BANNED" -gt 0 ] 2>/dev/null; then
                    echo "[$jail] Banned: $BANNED"
                    fail2ban-client status "$jail" 2>/dev/null | grep "Banned IP list:" | sed 's/.*Banned IP list:\s*//' | tr ' ' '\n'
                fi
            fi
        done
        ;;
        
    summary)
        # Quick summary
        echo "=== Fail2ban Summary ==="
        
        # Check if fail2ban is running
        if ! fail2ban-client status >/dev/null 2>&1; then
            echo "Status: NOT RUNNING"
            exit 0
        fi
        
        echo "Status: RUNNING"
        
        # Count jails
        JAIL_COUNT=$(fail2ban-client status 2>/dev/null | grep "Jail list:" | sed 's/.*Jail list:\s*//' | tr ',' '\n' | wc -l)
        echo "Active Jails: $JAIL_COUNT"
        
        # Count total banned IPs
        TOTAL_BANNED=0
        fail2ban-client status 2>/dev/null | grep "Jail list:" | sed 's/.*Jail list:\s*//' | tr ',' '\n' | sed 's/^\s*//' | while read jail; do
            if [ -n "$jail" ]; then
                BANNED=$(fail2ban-client status "$jail" 2>/dev/null | grep "Currently banned" | awk '{print $4}')
                TOTAL_BANNED=$((TOTAL_BANNED + BANNED))
            fi
        done
        echo "Total Banned IPs: $TOTAL_BANNED"
        ;;
        
    test)
        # Test if fail2ban is accessible
        if fail2ban-client status >/dev/null 2>&1; then
            echo "SUCCESS: Fail2ban is running and accessible"
            fail2ban-client status 2>/dev/null
        else
            echo "ERROR: Cannot connect to fail2ban"
            exit 1
        fi
        ;;
        
    *)
        echo "ERROR: Invalid action: $ACTION"
        echo ""
        echo "Usage: $0 {action} [jail-name]"
        echo ""
        echo "Available actions:"
        echo "  status           - General fail2ban status"
        echo "  jail-list        - List all active jails"
        echo "  jail-status      - Status for specific jail (requires jail name)"
        echo "  all-jails        - Status for all jails"
        echo "  banned-ips       - List all currently banned IPs"
        echo "  summary          - Quick summary"
        echo "  test             - Test if fail2ban is accessible"
        echo ""
        echo "Examples:"
        echo "  $0 status"
        echo "  $0 jail-status sshd"
        echo "  $0 banned-ips"
        exit 1
        ;;
esac

exit 0
