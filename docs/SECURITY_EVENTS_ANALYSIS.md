# Security Events Widget - Analyse og Anbefalinger

**Dato:** 11. oktober 2025  
**Status:** ⚠️ Widget får INGEN data på grunn av permissions-problemer

---

## 📊 Nåværende Status

### Data Innsamling (Current State)
```
Total events: 0
Last hour: 0
Last 24h: 0
Critical: 0
Warnings: 0
Unique IPs: 0
Fail2ban: Not accessible
```

### Identifiserte Problemer

#### 1. **SSH Failed Logins - Permission Denied** 🔴
- **Fil:** `/var/log/auth.log`
- **Problem:** Web-bruker (`psaadm`/`www-data`) har ikke lesetilgang
- **Filrettigheter:** `-rw-r----- 1 syslog adm`
- **Error:** `Permission denied`

**Konsekvens:** Widget kan ikke se SSH brute-force forsøk, noe som er KRITISK for sikkerhet.

#### 2. **Fail2ban Status - Socket Permission Denied** 🔴
- **Socket:** `/var/run/fail2ban/fail2ban.sock`
- **Problem:** Kun root kan lese fail2ban socket
- **Error:** `Permission denied to socket (you must be root)`

**Konsekvens:** Kan ikke se hvor mange IP-adresser som er banned, eller status på jails.

#### 3. **Laravel Auth Failures - Ingen Data** 🟡
- **Fil:** `storage/logs/laravel.log`
- **Status:** Tilgjengelig, men ingen auth failures (som er bra!)
- **Kommentar:** Dette er faktisk positivt - ingen innloggingsfeil.

#### 4. **Nginx Access Log - Ikke Testet** ⚪
- **Fil:** `/var/log/nginx/access.log`
- **Status:** Sannsynligvis samme permissions-problem som auth.log

---

## 🎯 Anbefalte Tillegg (Nummerert Liste)

### **Tillegg #1: Logg-tilgang via Sudo Wrapper** (Høy prioritet)
**Problem:** Web-bruker kan ikke lese system-logger  
**Løsning:** Lag en sudo-wrapper for sikker tilgang til logger

**Implementasjon:**
```bash
# /usr/local/bin/security-log-reader.sh
#!/bin/bash
# Safe wrapper for reading security logs

case "$1" in
    ssh-failed)
        grep 'Failed password' /var/log/auth.log 2>/dev/null | tail -n 50
        ;;
    ssh-successful)
        grep 'Accepted password\|Accepted publickey' /var/log/auth.log 2>/dev/null | tail -n 20
        ;;
    suspicious-ips)
        grep -E '(SELECT|UNION|<script|\.\./)' /var/log/nginx/access.log 2>/dev/null | tail -n 30
        ;;
    *)
        echo "Invalid option"
        exit 1
        ;;
esac
```

**Sudo konfiguration:**
```
# /etc/sudoers.d/security-widget
psaadm ALL=(ALL) NOPASSWD: /usr/local/bin/security-log-reader.sh *
www-data ALL=(ALL) NOPASSWD: /usr/local/bin/security-log-reader.sh *
```

**Fordeler:**
- ✅ Sikker tilgang til logger
- ✅ Kun read-only operasjoner
- ✅ Auditert via sudoers
- ✅ Ingen direkte filsystem-tilgang

---

### **Tillegg #2: Fail2ban Status via API** (Høy prioritet)
**Problem:** Fail2ban socket krever root-tilgang  
**Løsning:** Bruk `fail2ban-client` via sudo wrapper

**Implementasjon:**
```bash
# /usr/local/bin/fail2ban-status.sh
#!/bin/bash
# Safe wrapper for fail2ban status

case "$1" in
    status)
        fail2ban-client status 2>/dev/null
        ;;
    jail-list)
        fail2ban-client status | grep "Jail list" | sed 's/.*Jail list://' | tr ',' '\n'
        ;;
    jail-status)
        if [ -n "$2" ]; then
            fail2ban-client status "$2" 2>/dev/null
        fi
        ;;
    *)
        echo "Usage: $0 {status|jail-list|jail-status <jail>}"
        exit 1
        ;;
esac
```

**Sudo konfiguration:**
```
# /etc/sudoers.d/fail2ban-widget
psaadm ALL=(ALL) NOPASSWD: /usr/local/bin/fail2ban-status.sh *
www-data ALL=(ALL) NOPASSWD: /usr/local/bin/fail2ban-status.sh *
```

---

### **Tillegg #3: GeoIP Tracking for Trusler** (Medium prioritet)
**Formål:** Vis geografisk opprinnelse av angrep

**Implementasjon:**
```bash
# Installer GeoIP database
apt-get install geoip-bin geoip-database

# I SecurityEventsFetcher.php
protected function getCountryForIp(string $ip): string
{
    $result = ReadonlyCommand::run("geoiplookup {$ip}");
    if ($result['success'] && preg_match('/Country Edition: (\w+)/', $result['output'], $m)) {
        return $m[1];
    }
    return 'Unknown';
}
```

**Widget Visning:**
```php
// Legg til i event data:
'country' => $this->getCountryForIp($ip),
'country_flag' => $this->getCountryFlag($countryCode),
```

**UI Forbedring:**
- Vis verdenskart med angrep
- Top 5 angrepende land (🇨🇳 China, 🇷🇺 Russia, osv.)
- Fargekoding basert på antall forsøk

---

### **Tillegg #4: Risikovurdering Score** (Medium prioritet)
**Formål:** Automatisk risiko-scoring av security events

**Implementasjon:**
```php
protected function calculateRiskScore(array $summary): array
{
    $score = 0;
    $factors = [];
    
    // SSH brute force (høy risiko)
    if ($summary['by_type']['ssh_failed_login'] > 10) {
        $score += 30;
        $factors[] = "Høy SSH brute-force aktivitet";
    }
    
    // Suspicious requests (kritisk)
    if ($summary['by_type']['suspicious_request'] > 5) {
        $score += 40;
        $factors[] = "Aktive SQL/XSS angrep";
    }
    
    // Unique attacking IPs
    if ($summary['unique_ip_count'] > 20) {
        $score += 20;
        $factors[] = "Distribuert angrep ({$summary['unique_ip_count']} IPs)";
    }
    
    // Recent activity (siste time)
    if ($summary['last_hour'] > 15) {
        $score += 10;
        $factors[] = "Høy aktivitet nå ({$summary['last_hour']} events)";
    }
    
    return [
        'score' => min(100, $score),
        'level' => $score < 30 ? 'LOW' : ($score < 60 ? 'MEDIUM' : 'HIGH'),
        'factors' => $factors,
        'recommendation' => $score > 60 ? 'IMMEDIATE ACTION REQUIRED' : 'Monitor',
    ];
}
```

**Widget Visning:**
```html
<!-- Risk Score Indicator -->
<div class="risk-meter">
    <div class="risk-score {{ $riskLevel }}">
        {{ $riskScore }}/100
    </div>
    <div class="risk-factors">
        @foreach($factors as $factor)
            <div class="factor">⚠️ {{ $factor }}</div>
        @endforeach
    </div>
</div>
```

---

### **Tillegg #5: IP Reputation Check** (Medium prioritet)
**Formål:** Sjekk om IP-adresser er kjente trusler

**Tjenester:**
- AbuseIPDB (gratis API)
- IPQualityScore
- Blacklist-sjekk (Spamhaus, etc.)

**Implementasjon:**
```php
protected function checkIpReputation(string $ip): array
{
    // Cache results to avoid API spam
    $cacheKey = "ip_reputation:{$ip}";
    
    return Cache::remember($cacheKey, 3600, function() use ($ip) {
        // Check AbuseIPDB
        $apiKey = config('security.abuseipdb_key');
        if (!$apiKey) return ['checked' => false];
        
        $response = Http::get('https://api.abuseipdb.com/api/v2/check', [
            'ipAddress' => $ip,
            'maxAgeInDays' => 90,
        ])->json();
        
        return [
            'checked' => true,
            'abuse_score' => $response['data']['abuseConfidenceScore'] ?? 0,
            'is_whitelisted' => $response['data']['isWhitelisted'] ?? false,
            'country' => $response['data']['countryCode'] ?? 'Unknown',
            'isp' => $response['data']['isp'] ?? 'Unknown',
        ];
    });
}
```

**Widget Forbedring:**
- Rød advarsel for kjente botnet IP-er
- Trust score for hver IP
- Automatisk blokkering av høy-risiko IP-er

---

### **Tillegg #6: Rate Limit Violations Tracking** (Lav prioritet)
**Formål:** Overvåk Laravel rate limiting

**Implementasjon:**
```php
protected function getRateLimitViolations(): array
{
    $events = [];
    
    // Check Laravel log for rate limit hits
    $result = ReadonlyCommand::run("grep 'Too Many Attempts' storage/logs/laravel.log 2>/dev/null | tail -n 20");
    
    if ($result['success']) {
        // Parse rate limit violations
        // Extract IP, route, timestamp
    }
    
    return $events;
}
```

---

### **Tillegg #7: Security Event Notifications** (Lav prioritet)
**Formål:** Real-time varsling ved kritiske events

**Implementasjon:**
```php
// I SecurityEventsFetcher.php
protected function checkCriticalEvents(): void
{
    $summary = $this->getSummary();
    
    // Critical threshold: > 50 failed logins siste time
    if ($summary['last_hour'] > 50) {
        Notification::send(
            User::admins(),
            new SecurityAlertNotification([
                'type' => 'brute_force',
                'count' => $summary['last_hour'],
                'severity' => 'critical',
            ])
        );
    }
}
```

**Kanaler:**
- Email (Laravel Mail)
- Slack webhook
- SMS (Twilio) for kritiske events
- Push notification (via PWA)

---

### **Tillegg #8: Historical Trends & Analytics** (Lav prioritet)
**Formål:** Langtidsovervåking og trendanalyse

**Implementasjon:**
```php
// Lagre events i database for historikk
Schema::create('security_events', function (Blueprint $table) {
    $table->id();
    $table->string('type');
    $table->string('severity');
    $table->string('ip')->index();
    $table->string('country')->nullable();
    $table->text('message');
    $table->json('metadata')->nullable();
    $table->timestamp('occurred_at')->index();
    $table->timestamps();
});
```

**Analytics:**
- Attack patterns by time of day
- Top attacking countries over time
- Seasonal trends
- Correlation with other events

---

## 🚀 Implementeringsplan (Prioritert)

### **Fase 1: Kritisk (Denne uken)**
1. ✅ **Tillegg #1:** Sudo wrapper for logger
2. ✅ **Tillegg #2:** Fail2ban sudo wrapper
3. Test at widget får data

### **Fase 2: Viktig (Neste uke)**
4. **Tillegg #4:** Risikovurdering score
5. **Tillegg #3:** GeoIP tracking
6. UI forbedringer basert på ny data

### **Fase 3: Forbedringer (Måned 1)**
7. **Tillegg #5:** IP Reputation check
8. **Tillegg #7:** Real-time notifications

### **Fase 4: Langtidsovervåking (Måned 2+)**
9. **Tillegg #8:** Historical database
10. **Tillegg #6:** Rate limit tracking
11. Advanced analytics og rapporter

---

## 📋 Umiddelbare Handlinger (Action Items)

### **For System Administrator:**
```bash
# 1. Lag sudo wrappers
sudo nano /usr/local/bin/security-log-reader.sh
sudo chmod +x /usr/local/bin/security-log-reader.sh

sudo nano /usr/local/bin/fail2ban-status.sh
sudo chmod +x /usr/local/bin/fail2ban-status.sh

# 2. Konfigurer sudoers
sudo visudo -f /etc/sudoers.d/security-widget
sudo visudo -f /etc/sudoers.d/fail2ban-widget

# 3. Test tilgang
sudo -u psaadm /usr/local/bin/security-log-reader.sh ssh-failed
sudo -u psaadm /usr/local/bin/fail2ban-status.sh status
```

### **For Utvikler (AI):**
```bash
# 1. Oppdater ReadonlyCommand whitelist
# Legg til: 'sudo /usr/local/bin/security-log-reader.sh'
# Legg til: 'sudo /usr/local/bin/fail2ban-status.sh'

# 2. Oppdater SecurityEventsFetcher
# Bytt fra direkte logg-lesing til sudo wrappers

# 3. Legg til GeoIP støtte
apt-get install geoip-bin geoip-database

# 4. Implementer risk scoring
# Følg kode i Tillegg #4
```

---

## 🎯 Forventet Resultat

### **Etter Fase 1:**
```
Total events: 50-100 (realistisk)
Last hour: 5-10
Last 24h: 50-100
Critical: 2-5 (SQL injection forsøk)
Warnings: 20-30 (SSH failed logins)
Unique IPs: 10-20
Fail2ban:
  Installed: Yes
  Running: Yes
  Total banned: 15-25
  Jails: [sshd, nginx-limit-req, etc.]
```

### **Etter Fase 2:**
- Risk score: Kalkulert (0-100)
- GeoIP: Viser land for alle IP-er
- Top 5 angrepende land visualisert
- Fargekodet severity

### **Etter Fase 3:**
- IP reputation scores
- Real-time Slack notifications
- Automatisk high-risk IP blokkering

---

## 📊 Konklusjon

**Nåværende Status:** Widget er teknisk korrekt implementert, men får INGEN data pga. permissions.

**Root Cause:** System-logger krever root/sudo tilgang.

**Løsning:** Sikre sudo wrappers (Tillegg #1 og #2) må implementeres FØRST.

**Anbefaling:** Start med Fase 1 (sudo wrappers), deretter bygge ut med risikovurdering, GeoIP og notifications.

**Estimert Tid:**
- Fase 1: 2-4 timer (systemadmin + utvikling)
- Fase 2: 4-6 timer (utvikling)
- Fase 3: 6-8 timer (API integrasjoner)
- Fase 4: 8-12 timer (database + analytics)

**Total:** ~20-30 timer for komplett security monitoring system.
