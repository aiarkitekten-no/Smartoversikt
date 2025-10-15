#!/bin/bash
# Security Log Reader Wrapper
# Safe read-only access to system security logs for widget
# Location: /usr/local/bin/security-log-reader.sh
# Permissions: chmod +x /usr/local/bin/security-log-reader.sh
# Sudoers: psaadm ALL=(ALL) NOPASSWD: /usr/local/bin/security-log-reader.sh *

ACTION="$1"

case "$ACTION" in
    ssh-failed)
        # Get recent SSH failed login attempts
        grep 'Failed password' /var/log/auth.log 2>/dev/null | tail -n 50
        ;;
        
    ssh-successful)
        # Get recent successful SSH logins
        grep 'Accepted password\|Accepted publickey' /var/log/auth.log 2>/dev/null | tail -n 20
        ;;
        
    ssh-invalid-user)
        # Invalid user attempts (often indicates scanning)
        grep 'Invalid user' /var/log/auth.log 2>/dev/null | tail -n 30
        ;;
        
    nginx-suspicious)
        # Suspicious requests (SQL injection, XSS, path traversal)
        grep -E '(SELECT|UNION|INSERT|UPDATE|DELETE|DROP|<script|javascript:|onerror=|\.\./|%00)' \
            /var/log/nginx/access.log 2>/dev/null | tail -n 50
        ;;
        
    nginx-404)
        # High 404 rate (scanning/enumeration)
        grep ' 404 ' /var/log/nginx/access.log 2>/dev/null | tail -n 30
        ;;
        
    nginx-errors)
        # Recent nginx errors
        tail -n 50 /var/log/nginx/error.log 2>/dev/null
        ;;
    
    vhost-access)
        # Read a specific vhost access log safely
        # Usage: security-log-reader.sh vhost-access <domain> [lines]
        DOMAIN="$2"
        LINES=${3:-200}
        # Basic validation: domain-like and no path traversal
        if [[ -z "$DOMAIN" ]] || [[ "$DOMAIN" =~ / ]] || [[ "$DOMAIN" =~ ".." ]]; then
            echo "ERROR: Invalid domain"
            exit 2
        fi
        LOG="/var/www/vhosts/${DOMAIN}/logs/access.log"
        if [ ! -f "$LOG" ]; then
            # Plesk rotates to access_log / proxy_access_log
            LOG="/var/www/vhosts/${DOMAIN}/logs/access_log"
        fi
        if [ ! -f "$LOG" ]; then
            LOG="/var/www/vhosts/${DOMAIN}/logs/proxy_access_log"
        fi
        if [ -f "$LOG" ]; then
            tail -n "$LINES" "$LOG" 2>/dev/null
        else
            echo "ERROR: Access log not found for $DOMAIN"
            exit 3
        fi
        ;;
    
    vhost-error)
        # Read a specific vhost error log safely
        # Usage: security-log-reader.sh vhost-error <domain> [lines]
        DOMAIN="$2"
        LINES=${3:-100}
        if [[ -z "$DOMAIN" ]] || [[ "$DOMAIN" =~ / ]] || [[ "$DOMAIN" =~ ".." ]]; then
            echo "ERROR: Invalid domain"
            exit 2
        fi
        LOG="/var/www/vhosts/${DOMAIN}/logs/error.log"
        if [ ! -f "$LOG" ]; then
            LOG="/var/www/vhosts/${DOMAIN}/logs/error_log"
        fi
        if [ -f "$LOG" ]; then
            tail -n "$LINES" "$LOG" 2>/dev/null
        else
            echo "ERROR: Error log not found for $DOMAIN"
            exit 3
        fi
        ;;
        
    auth-summary)
        # Quick summary of auth activity
        echo "=== Auth Summary (Last 100 lines) ==="
        tail -n 100 /var/log/auth.log 2>/dev/null | grep -c "Failed password" | xargs -I{} echo "Failed SSH logins: {}"
        tail -n 100 /var/log/auth.log 2>/dev/null | grep -c "Accepted password\|Accepted publickey" | xargs -I{} echo "Successful SSH logins: {}"
        tail -n 100 /var/log/auth.log 2>/dev/null | grep -c "Invalid user" | xargs -I{} echo "Invalid user attempts: {}"
        ;;
        
    test)
        # Test command for debugging
        echo "Security log reader is working!"
        echo "Available commands: ssh-failed, ssh-successful, ssh-invalid-user, nginx-suspicious, nginx-404, nginx-errors, auth-summary"
        ;;
        
    *)
        echo "ERROR: Invalid action: $ACTION"
        echo ""
        echo "Usage: $0 {action}"
        echo ""
        echo "Available actions:"
        echo "  ssh-failed        - Recent SSH failed login attempts"
        echo "  ssh-successful    - Recent successful SSH logins"
        echo "  ssh-invalid-user  - Invalid user login attempts"
        echo "  nginx-suspicious  - Suspicious web requests (SQL/XSS/traversal)"
        echo "  nginx-404         - Recent 404 errors"
        echo "  nginx-errors      - Recent nginx error log"
    echo "  vhost-access      - Tail a vhost access log (domain [lines])"
    echo "  vhost-error       - Tail a vhost error log (domain [lines])"
        echo "  auth-summary      - Quick summary of auth activity"
        echo "  test              - Test if script is working"
        exit 1
        ;;
esac

exit 0
