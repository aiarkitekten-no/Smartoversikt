# Security Events Widget - Installation Guide

## 📋 Prerequisites
- Root/sudo access
- fail2ban installed (optional but recommended)
- System logs accessible

## 🚀 Installation Steps

### Step 1: Copy Wrapper Scripts to System

```bash
# As root or with sudo
cd /var/www/vhosts/smartesider.no/nytt.smartesider.no

# Copy scripts to system bin
sudo cp scripts/security-log-reader.sh /usr/local/bin/
sudo cp scripts/fail2ban-status.sh /usr/local/bin/

# Set correct permissions
sudo chmod +x /usr/local/bin/security-log-reader.sh
sudo chmod +x /usr/local/bin/fail2ban-status.sh

# Set ownership
sudo chown root:root /usr/local/bin/security-log-reader.sh
sudo chown root:root /usr/local/bin/fail2ban-status.sh
```

### Step 2: Configure Sudoers

```bash
# Create sudoers file for security widget
sudo nano /etc/sudoers.d/security-widget
```

**Add the following content:**

```bash
# Security Widget - Log Reader Access
# Allow web server users to read security logs via wrapper script
psaadm ALL=(ALL) NOPASSWD: /usr/local/bin/security-log-reader.sh *
www-data ALL=(ALL) NOPASSWD: /usr/local/bin/security-log-reader.sh *

# Security Widget - Fail2ban Access
# Allow web server users to check fail2ban status
psaadm ALL=(ALL) NOPASSWD: /usr/local/bin/fail2ban-status.sh *
www-data ALL=(ALL) NOPASSWD: /usr/local/bin/fail2ban-status.sh *
```

**Set correct permissions:**

```bash
sudo chmod 0440 /etc/sudoers.d/security-widget
sudo visudo -c  # Validate syntax
```

### Step 3: Test Sudo Access

```bash
# Switch to web user
sudo -u psaadm -s

# Test security log reader
sudo /usr/local/bin/security-log-reader.sh test
sudo /usr/local/bin/security-log-reader.sh ssh-failed
sudo /usr/local/bin/security-log-reader.sh auth-summary

# Test fail2ban status
sudo /usr/local/bin/fail2ban-status.sh test
sudo /usr/local/bin/fail2ban-status.sh summary
sudo /usr/local/bin/fail2ban-status.sh banned-ips

# Exit web user shell
exit
```

**Expected Output:**
```
Security log reader is working!
Available commands: ssh-failed, ssh-successful, ...

SUCCESS: Fail2ban is running and accessible
Status
|- Number of jail:      3
`- Jail list:   sshd, nginx-limit-req, wordpress
```

### Step 4: Update ReadonlyCommand Whitelist

Edit: `app/Support/Sys/ReadonlyCommand.php`

```php
protected static array $whitelist = [
    // ... existing entries ...
    
    // Security widget wrappers
    'sudo /usr/local/bin/security-log-reader.sh',
    'sudo /usr/local/bin/fail2ban-status.sh',
    'geoiplookup',  // For GeoIP tracking (optional)
];
```

### Step 5: Update SecurityEventsFetcher

Edit: `app/Services/Widgets/SecurityEventsFetcher.php`

```php
protected function getSshFailedLogins(): array
{
    $events = [];
    
    // Use sudo wrapper instead of direct file access
    $result = ReadonlyCommand::run("sudo /usr/local/bin/security-log-reader.sh ssh-failed");
    
    if (!$result['success']) {
        return $events;
    }

    // ... rest of method unchanged
}

protected function getFail2banStatus(): ?array
{
    // Use sudo wrapper
    $result = ReadonlyCommand::run('sudo /usr/local/bin/fail2ban-status.sh status');
    
    if (!$result['success']) {
        return [
            'installed' => false,
            'running' => false,
        ];
    }

    // ... parse result
}
```

### Step 6: Clear Cache & Test Widget

```bash
# Clear Laravel cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Test widget data fetch
php artisan tinker --execute='
$fetcher = new \App\Services\Widgets\SecurityEventsFetcher();
$data = $fetcher->fetch();
echo "Total events: " . count($data["events"]) . PHP_EOL;
echo "Fail2ban running: " . ($data["fail2ban"]["running"] ? "YES" : "NO") . PHP_EOL;
echo "Total banned: " . ($data["fail2ban"]["total_banned"] ?? 0) . PHP_EOL;
'
```

**Expected Output:**
```
Total events: 15-50 (depending on server activity)
Fail2ban running: YES
Total banned: 5-20
```

## 🔧 Troubleshooting

### Issue: "Permission denied" when testing

**Solution:**
```bash
# Check sudoers file permissions
ls -l /etc/sudoers.d/security-widget
# Should be: -r--r----- 1 root root (0440)

# Validate sudoers syntax
sudo visudo -c

# Check wrapper script permissions
ls -l /usr/local/bin/security-log-reader.sh
# Should be: -rwxr-xr-x 1 root root
```

### Issue: "Command not whitelisted"

**Solution:**
```bash
# Check ReadonlyCommand whitelist
grep -A 5 "protected static array \$whitelist" app/Support/Sys/ReadonlyCommand.php

# Make sure these are included:
# 'sudo /usr/local/bin/security-log-reader.sh',
# 'sudo /usr/local/bin/fail2ban-status.sh',
```

### Issue: "Fail2ban not running"

**Solution:**
```bash
# Check if fail2ban is installed
which fail2ban-client

# Check if fail2ban service is running
sudo systemctl status fail2ban

# Start fail2ban if stopped
sudo systemctl start fail2ban
sudo systemctl enable fail2ban
```

### Issue: Still getting 0 events

**Solution:**
```bash
# Test wrapper directly as web user
sudo -u psaadm sudo /usr/local/bin/security-log-reader.sh ssh-failed

# Check if logs have any data
sudo grep "Failed password" /var/log/auth.log | wc -l

# Check log file permissions
ls -l /var/log/auth.log
# Should be readable by syslog/adm group

# Test Laravel command execution
cd /var/www/vhosts/smartesider.no/nytt.smartesider.no
php artisan tinker --execute='
$r = \App\Support\Sys\ReadonlyCommand::run("sudo /usr/local/bin/security-log-reader.sh test");
var_dump($r);
'
```

## ✅ Verification Checklist

- [ ] Wrapper scripts copied to `/usr/local/bin/`
- [ ] Scripts are executable (`chmod +x`)
- [ ] Sudoers file created at `/etc/sudoers.d/security-widget`
- [ ] Sudoers permissions set to 0440
- [ ] Sudoers syntax validated (`sudo visudo -c`)
- [ ] Test as web user successful
- [ ] ReadonlyCommand whitelist updated
- [ ] SecurityEventsFetcher updated to use sudo wrappers
- [ ] Cache cleared
- [ ] Widget shows data on dashboard

## 📊 Expected Results After Installation

```
Security Events Widget Dashboard:

📊 Summary:
   Total events: 45
   Last hour: 8
   Last 24h: 45
   Critical: 3 (SQL injection attempts)
   Warnings: 25 (SSH failed logins)
   Unique IPs: 12

🛡️ Fail2ban Status:
   ✓ Running
   Active Jails: 3
   Total Banned: 15 IPs

📋 Recent Events:
   🔴 SSH login feilet for bruker 'root' (185.220.101.45)
   🔴 Mistenkelig forespørsel (sql forsøk) (192.168.1.100)
   🟡 SSH login feilet for bruker 'admin' (203.0.113.45)
   ...
```

## 🔒 Security Notes

- Wrappers are read-only (no write/delete operations)
- Sudo access limited to specific scripts
- No shell access granted
- All commands logged via sudo
- Scripts validate input to prevent injection

## 📞 Support

If issues persist, check:
1. `storage/logs/laravel.log` for PHP errors
2. `/var/log/syslog` for sudo errors
3. Widget error state on dashboard
