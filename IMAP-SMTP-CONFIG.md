# IMAP/SMTP Widget Configuration Guide

## Overview
The mail widgets can be configured to monitor your email server via IMAP and SMTP protocols.

## Configuration

### 1. Environment Variables

Add the following to your `.env` file:

```bash
# IMAP Settings (for mailbox monitoring)
IMAP_HOST=mail.smartesider.no
IMAP_PORT=993
IMAP_USERNAME=your-email@smartesider.no
IMAP_PASSWORD=your-secure-password
IMAP_ENCRYPTION=ssl

# SMTP Settings (for server monitoring)
SMTP_HOST=smtp.smartesider.no
SMTP_PORT=587
SMTP_USERNAME=your-email@smartesider.no
SMTP_ENCRYPTION=tls

# Weather Location (Moss, Østfold)
WEATHER_LAT=59.4344
WEATHER_LON=10.6574
```

### 2. Encryption Options

#### IMAP
- **Port 993**: Use `IMAP_ENCRYPTION=ssl`
- **Port 143**: Use `IMAP_ENCRYPTION=tls` or leave blank for no encryption

#### SMTP
- **Port 465**: Use `SMTP_ENCRYPTION=ssl`
- **Port 587**: Use `SMTP_ENCRYPTION=tls`
- **Port 25**: Leave blank for no encryption

### 3. Common Providers

#### Gmail
```bash
IMAP_HOST=imap.gmail.com
IMAP_PORT=993
IMAP_ENCRYPTION=ssl

SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls
```

**Note**: Requires App Password if 2FA is enabled.

#### Outlook/Office 365
```bash
IMAP_HOST=outlook.office365.com
IMAP_PORT=993
IMAP_ENCRYPTION=ssl

SMTP_HOST=smtp.office365.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls
```

#### Plesk/Generic Hosting
```bash
IMAP_HOST=mail.yourdomain.com
IMAP_PORT=993
IMAP_ENCRYPTION=ssl

SMTP_HOST=mail.yourdomain.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls
```

### 4. Widget-Specific Settings

You can also configure per-widget settings in the widget settings modal (⚙️ button):

```json
{
  "imap_host": "custom-server.com",
  "imap_port": 993,
  "imap_username": "specific@email.com",
  "imap_password": "password",
  "imap_encryption": "ssl"
}
```

## Available Mail Widgets

### 1. IMAP Mailbox (`mail.imap`)
- Total messages
- Unread count
- New messages today
- Recent messages
- Mailbox size estimate

**Requires**: IMAP credentials configured

### 2. Mail Queue (`mail.queue`)
- Laravel queue pending/failed
- Postfix queue (deferred/active)
- Total pending count

**Requires**: Database access (Laravel) + optional Postfix access

### 3. Failed Jobs (`mail.failed-jobs`)
- Total failed jobs
- Recent 24h failures
- Latest failure details
- Failures by queue

**Requires**: Database with `failed_jobs` table

### 4. Mail Log (`mail.log`)
- Sent/received count
- Bounced messages
- Rejected messages
- Deferred messages

**Requires**: Access to mail logs (`/var/log/mail.log`)

### 5. SMTP Status (`mail.smtp`)
- Postfix running status
- Active SMTP connections
- Queue size

**Requires**: System access to Postfix

## Security Best Practices

1. **Use App Passwords**: For Gmail/Outlook, create app-specific passwords
2. **Restrict Permissions**: Create dedicated monitoring accounts with read-only access
3. **Encrypt Credentials**: Use Laravel's encrypted environment variables if available
4. **Regular Rotation**: Change passwords periodically
5. **Monitor Access**: Check mail server logs for unusual activity

## Troubleshooting

### "IMAP ikke konfigurert"
- Check `.env` file has all IMAP_* variables
- Run `php artisan config:clear`
- Verify credentials with a mail client first

### "Kunne ikke koble til IMAP-server"
- Check firewall allows outbound connections on IMAP port
- Verify hostname resolves: `ping $IMAP_HOST`
- Check credentials are correct
- Ensure encryption setting matches port

### "Connection timeout"
- Port might be blocked by firewall
- Try alternative ports (143 for IMAP, 25/587 for SMTP)
- Check server allows connections from your IP

### PHP IMAP Extension Missing
```bash
# Install PHP IMAP extension
sudo apt-get install php-imap
sudo systemctl restart php-fpm
```

## Testing Configuration

Test IMAP connection manually:
```bash
php artisan tinker
>>> $mbox = imap_open('{mail.server.com:993/imap/ssl}INBOX', 'user@domain.com', 'password');
>>> imap_check($mbox);
>>> imap_close($mbox);
```

Test widget refresh:
```bash
php artisan widgets:refresh mail.imap --force
```

## Widget Refresh Intervals

- `mail.imap`: 5 minutes (300s)
- `mail.queue`: 1 minute (60s)
- `mail.failed-jobs`: 2 minutes (120s)
- `mail.log`: 3 minutes (180s)
- `mail.smtp`: 1 minute (60s)

Adjust in widget settings (⚙️) if needed.

## Example `.env` Configuration

```bash
# Production settings for Smartesider
IMAP_HOST=mail.smartesider.no
IMAP_PORT=993
IMAP_USERNAME=monitor@smartesider.no
IMAP_PASSWORD=SecurePassword123!
IMAP_ENCRYPTION=ssl

SMTP_HOST=smtp.smartesider.no
SMTP_PORT=587
SMTP_USERNAME=monitor@smartesider.no
SMTP_ENCRYPTION=tls

# Weather for Moss, Østfold
WEATHER_LAT=59.4344
WEATHER_LON=10.6574
```

## Support

For issues, check:
1. Laravel logs: `storage/logs/laravel-*.log`
2. Widget snapshots: Database table `widget_snapshots`
3. Error messages in widget display

Contact: terje@smartesider.no
