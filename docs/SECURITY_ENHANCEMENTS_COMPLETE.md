# Security Events Widget - Enhancements Complete ✅

**Dato:** 2025-01-15  
**Widget:** Security Events  
**Status:** ✅ Implementert (Krever installasjon av dependencies)

---

## 🎯 Implementerte Tillegg

### ✅ Tillegg #3: GeoIP Tracking
**Funksjonalitet:** Viser landsflagg og lokasjon for hver angripende IP-adresse

**Implementert:**
- `getCountryForIp(string $ip)` - GeoIP lookup via `geoiplookup` kommando
- `getCountryFlag(string $code)` - Konverterer landskode til emoji-flagg (🇺🇸, 🇨🇳, 🇷🇺, etc.)
- 28 vanlige land mappet med flagg-emoji
- Fallback til 🌍 for ukjente land

**Vises i widget:**
- Landsflagg ved hver IP-adresse
- Landsnavn i parentes (f.eks. "🇨🇳 123.45.67.89 (China)")

**Krever:**
```bash
# Som root:
apt-get update
apt-get install -y geoip-bin geoip-database geoip-database-extra

# Test:
geoiplookup 8.8.8.8
# Output: GeoIP Country Edition: US, United States
```

---

### ✅ Tillegg #4: Risikovurdering (Risk Scoring)
**Funksjonalitet:** Automatisk beregning av risikoscore 0-100 basert på sikkerhetshendelser

**Vektlagt scoring:**
- **SSH brute-force (vekt: 30%):**
  - > 20 forsøk: 30 poeng
  - 11-20 forsøk: 20 poeng
  - 1-10 forsøk: 10 poeng

- **Mistenkelige web-requests/SQL/XSS (vekt: 40%):**
  - > 10 angrep: 40 poeng
  - 6-10 angrep: 30 poeng
  - 1-5 angrep: 15 poeng

- **Distribuert angrep - antall unike IP-er (vekt: 20%):**
  - > 20 IP-er: 20 poeng
  - 11-20 IP-er: 15 poeng
  - 6-10 IP-er: 8 poeng

- **Nylig aktivitet - siste time (vekt: 10%):**
  - > 15 events: 10 poeng
  - 9-15 events: 7 poeng

**Risiko-nivåer:**
- **CRITICAL (70-100):** 🔴 Rød - Umiddelbar handling kreves
- **HIGH (40-69):** 🟠 Oransje - Undersøk og iverksett tiltak
- **MEDIUM (20-39):** 🟡 Gul - Overvåk nøye
- **LOW (0-19):** 🟢 Grønn - Normal overvåking

**Vises i widget:**
- Stor risikovurdering-banner øverst
- Score med fargekodet bakgrunn
- Top 2 risikofaktorer listet
- Anbefalinger basert på detekterte trusler

---

### ✅ Tillegg #5: IP Reputation Check
**Funksjonalitet:** Sjekk IP-adressers omdømme mot AbuseIPDB database

**Features:**
- HTTP API kall til AbuseIPDB.com
- Cache i 1 time for å unngå rate limits
- Viser abuse confidence score (0-100%)
- Henter ISP, land, domene, antall rapporter

**Abuse Score tolkning:**
- **75-100%:** ⚠️ Kjent malicious IP - Høy risiko
- **25-74%:** ⚡ Mistenkelig IP - Moderat risiko
- **0-24%:** ✓ Lav risiko / ukjent

**Vises i widget:**
- Fargekodet badge ved hver IP med abuse score
- Rød badge (⚠️) for farlige IP-er
- Oransje badge (⚡) for mistenkelige IP-er
- Grønn badge (✓) for trygge IP-er

**Krever:**
1. Gratis AbuseIPDB konto: https://www.abuseipdb.com/register
2. Hent API-nøkkel: https://www.abuseipdb.com/account/api
3. Legg til i `.env`:
```env
SECURITY_ABUSEIPDB_ENABLED=true
SECURITY_ABUSEIPDB_API_KEY=your_actual_api_key_here
```

**Rate limits (gratis tier):**
- 1,000 requests/dag
- Cache i 1 time reduserer requests betraktelig

---

### ✅ Tillegg #7: Notifications (Varsling)
**Funksjonalitet:** Automatiske varsler når kritiske sikkerhetshendelser oppdages

**Støttede kanaler:**
- **Email:** Standard Laravel Mail
- **Slack:** Webhook integration med rike meldinger

**Varslings-terskler (konfigurerbare):**

**KRITISK Varsling sendes ved:**
- > 50 events siste time (default)
- > 20 unike angripende IP-er
- > 5 SQL injection forsøk
- Risk Score = CRITICAL (70+)

**ADVARSEL Varsling sendes ved:**
- > 25 events siste time
- > 10 unike IP-er
- Risk Score = HIGH (40-69)

**Varsel innhold:**
- Risk score og nivå
- Events siste time og 24 timer
- Unike IP-er
- Detaljert oppdeling etter type
- Risikofaktorer og anbefalinger
- Link til dashboard

**Email format:**
- Plain text med strukturert layout
- Alle detaljer inkludert
- Timestampet
- Direkte link til dashboard

**Slack format:**
- Fargekodet attachment (rød/oransje basert på alvorlighet)
- Strukturerte felter
- Emoji-ikoner
- Slack-notifikasjon

**Konfigurasjon i `.env`:**
```env
# Enable notifications
SECURITY_NOTIFICATIONS_ENABLED=true

# Email notifications
SECURITY_EMAIL_TO=admin@smartesider.no
SECURITY_EMAIL_FROM=security@smartesider.no

# Slack notifications (valgfritt)
SECURITY_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL
SECURITY_SLACK_CHANNEL=#security-alerts
SECURITY_SLACK_USERNAME="Security Bot"
SECURITY_SLACK_ICON=:shield:

# Custom thresholds (valgfritt - defaults brukes hvis ikke satt)
SECURITY_THRESHOLD_CRITICAL_EVENTS_PER_HOUR=50
SECURITY_THRESHOLD_CRITICAL_UNIQUE_IPS=20
SECURITY_THRESHOLD_CRITICAL_SQL_INJECTION=5
SECURITY_THRESHOLD_WARNING_EVENTS_PER_HOUR=25
```

---

## 📋 Installasjonsinstruksjoner

### Steg 1: Installer GeoIP (Krever root)
```bash
# Som root eller med sudo:
apt-get update
apt-get install -y geoip-bin geoip-database geoip-database-extra

# Verifiser:
geoiplookup 8.8.8.8
# Forventet: GeoIP Country Edition: US, United States
```

### Steg 2: Konfigurer AbuseIPDB (Valgfritt, men anbefalt)
1. Opprett gratis konto: https://www.abuseipdb.com/register
2. Gå til API-nøkler: https://www.abuseipdb.com/account/api
3. Kopier API-nøkkel
4. Åpne `.env` filen:
```bash
nano /var/www/vhosts/smartesider.no/nytt.smartesider.no/.env
```
5. Legg til linjer:
```env
SECURITY_ABUSEIPDB_ENABLED=true
SECURITY_ABUSEIPDB_API_KEY=paste_your_actual_key_here
```

### Steg 3: Konfigurer Email Notifications (Valgfritt)
Legg til i `.env`:
```env
SECURITY_NOTIFICATIONS_ENABLED=true
SECURITY_EMAIL_TO=admin@smartesider.no
SECURITY_EMAIL_FROM=security@smartesider.no
```

### Steg 4: Konfigurer Slack (Valgfritt)
1. Opprett Slack Incoming Webhook:
   - Gå til: https://api.slack.com/messaging/webhooks
   - Følg instruksjonene for workspace
2. Kopier webhook URL
3. Legg til i `.env`:
```env
SECURITY_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/T00000000/B00000000/XXXXXXXXXXXXXXXXXXXX
SECURITY_SLACK_CHANNEL=#security-alerts
```

### Steg 5: Frontend er allerede bygget ✅
```bash
npm run build  # Allerede kjørt
```

---

## 🧪 Testing

### Test GeoIP
```bash
geoiplookup 8.8.8.8
geoiplookup 1.2.3.4
```

### Test widget data (uten GeoIP/AbuseIPDB):
```bash
cd /var/www/vhosts/smartesider.no/nytt.smartesider.no

php artisan tinker --execute='
$fetcher = new \App\Services\Widgets\SecurityEventsFetcher();
$data = $fetcher->fetch();
echo "Risk Score: " . $data["risk_score"]["score"] . "/100\n";
echo "Risk Level: " . $data["risk_score"]["level"] . "\n";
echo "Events: " . count($data["events"]) . "\n";
'
```

### Test med GeoIP (etter installasjon):
```bash
php artisan tinker --execute='
$fetcher = new \App\Services\Widgets\SecurityEventsFetcher();
$country = $fetcher->getCountryForIp("8.8.8.8");
print_r($country);
'
# Forventet: Array med code => US, name => United States, flag => 🇺🇸
```

### Test AbuseIPDB (etter API-nøkkel konfigurert):
```bash
php artisan tinker --execute='
$fetcher = new \App\Services\Widgets\SecurityEventsFetcher();
$reputation = $fetcher->checkIpReputation("118.25.6.39");
print_r($reputation);
'
# Forventet: Array med abuse_score, isp, country_code, etc.
```

---

## 📊 Nye Datafelt

### Events array (hver event):
```php
[
    'type' => 'ssh_failed_login',
    'severity' => 'warning',
    'ip' => '123.45.67.89',
    'user' => 'admin',
    'message' => 'SSH login feilet for bruker admin',
    'timestamp' => '2025-01-15T12:34:56Z',
    'relative_time' => '5 minutes ago',
    
    // NYE FELT:
    'country' => [
        'code' => 'CN',
        'name' => 'China',
        'flag' => '🇨🇳'
    ],
    'reputation' => [
        'checked' => true,
        'abuse_score' => 85,  // 0-100
        'is_whitelisted' => false,
        'country_code' => 'CN',
        'isp' => 'China Telecom',
        'domain' => 'chinatelecom.cn',
        'total_reports' => 1234
    ]
]
```

### Risk Score object:
```php
[
    'score' => 75,  // 0-100
    'level' => 'CRITICAL',  // LOW, MEDIUM, HIGH, CRITICAL
    'color' => 'red',  // green, yellow, orange, red
    'action' => 'IMMEDIATE ACTION REQUIRED',
    'factors' => [
        'Høy SSH brute-force aktivitet (25 forsøk)',
        'Aktive web-angrep (SQL/XSS: 12)',
        'Distribuert angrep (22 unike IP-er)'
    ],
    'recommendations' => [
        'Vurder å endre SSH port eller enable key-only auth',
        'Sjekk WAF-regler og vurder å blokkere angripende IP-er',
        'Vurder rate limiting og geografisk blokkering'
    ]
]
```

---

## 🔒 Sikkerhet

### ReadonlyCommand whitelist
Følgende kommandoer er whitelistet:
- ✅ `geoiplookup` - GeoIP lookup (read-only)
- ✅ `sudo /usr/local/bin/security-log-reader.sh` - Sikker log-lesing
- ✅ `sudo /usr/local/bin/fail2ban-status.sh` - Fail2ban status

### Sudo-konfigurasjon
Filen `/etc/sudoers.d/security-widget` gir NOPASSWD for:
- `psaadm`
- `www-data`
- `smartesider.no_2vii2f537vr`

Kun for de to whitelistede scriptene.

### API Sikkerhet
- AbuseIPDB API-nøkkel lagres i `.env` (ikke i git)
- Cache i 1 time reduserer API-kall
- Try-catch håndterer API-feil gracefully
- Logger feil uten å eksponere sensitive detaljer

---

## 📈 Performance

### Caching
- **IP Reputation:** 1 time cache (reduserer API-kall)
- **GeoIP:** Ingen cache (lokal database, raskt)

### API Rate Limits
- **AbuseIPDB Free Tier:** 1,000 requests/dag
- **Med 1t cache:** ~24 unike IP-er/dag = 24 requests
- **Margin:** 97.6% ubrukt kapasitet

---

## 🎨 UI/UX Forbedringer

### Risk Score Banner
- Stor, fargekodet banner øverst i widgeten
- Visuelt dominerende ved høy risiko
- Emoji-ikoner (🔴🟠🟡🟢)
- Top 2 risikofaktorer synlige

### Country Flags
- Landsflagg ved hver IP-adresse
- Landsnavn i parentes
- Intuitivt gjenkjennbart (🇨🇳, 🇷🇺, 🇺🇸, etc.)

### Reputation Badges
- Fargekodet badge med abuse score
- Rød (⚠️), Oransje (⚡), Grønn (✓)
- Prosentvis abuse confidence

### Color Coding
- **CRITICAL:** Rød bakgrunn (#DC2626)
- **HIGH:** Oransje bakgrunn (#EA580C)
- **MEDIUM:** Gul bakgrunn (#CA8A04)
- **LOW:** Grønn bakgrunn (#16A34A)

---

## 📝 Konfigurasjonsfil

`config/security.php` inneholder all konfigurasjon:

```php
return [
    'abuseipdb' => [
        'enabled' => env('SECURITY_ABUSEIPDB_ENABLED', false),
        'api_key' => env('SECURITY_ABUSEIPDB_API_KEY'),
        'cache_ttl' => 3600,  // 1 time
        'check_threshold' => 90,  // days to check
    ],
    
    'notifications' => [
        'enabled' => env('SECURITY_NOTIFICATIONS_ENABLED', false),
        'channels' => ['email', 'slack'],
        
        'email' => [
            'to' => env('SECURITY_EMAIL_TO'),
            'from' => env('SECURITY_EMAIL_FROM'),
        ],
        
        'slack' => [
            'webhook_url' => env('SECURITY_SLACK_WEBHOOK_URL'),
            'channel' => env('SECURITY_SLACK_CHANNEL', '#security-alerts'),
            'username' => env('SECURITY_SLACK_USERNAME', 'Security Bot'),
            'icon' => env('SECURITY_SLACK_ICON', ':shield:'),
        ],
        
        'thresholds' => [
            'critical' => [
                'events_per_hour' => 50,
                'unique_ips' => 20,
                'sql_injection_attempts' => 5,
            ],
            'warning' => [
                'events_per_hour' => 25,
                'unique_ips' => 10,
            ],
        ],
    ],
    
    'risk_scoring' => [
        'weights' => [
            'ssh_bruteforce' => 30,
            'sql_injection' => 40,
            'distributed_attack' => 20,
            'recent_activity' => 10,
        ],
    ],
    
    'geoip' => [
        'enabled' => true,
        'database_path' => '/usr/share/GeoIP/',
    ],
];
```

---

## ✅ Status

| Tillegg | Status | Dependencies | Konfigurasjon |
|---------|--------|--------------|---------------|
| #3 GeoIP Tracking | ✅ Implementert | ⏳ Installer `geoip-bin` | - |
| #4 Risikovurdering | ✅ Implementert | ✅ Ingen | ✅ Innebygd |
| #5 IP Reputation | ✅ Implementert | ✅ Ingen | ⏳ AbuseIPDB API-nøkkel |
| #7 Notifications | ✅ Implementert | ✅ Ingen | ⏳ Email/Slack config |

### Hva fungerer NÅ (uten ytterligere installasjon):
- ✅ Risikovurdering (Risk Score 0-100)
- ✅ Risk level og anbefalinger
- ✅ Fargekodet UI basert på risiko
- ✅ Fail2ban integrasjon (502 banned IPs)
- ✅ Security events parsing

### Hva krever installasjon:
- ⏳ GeoIP tracking → Installer `geoip-bin`
- ⏳ IP Reputation → Konfigurer AbuseIPDB API-nøkkel
- ⏳ Email notifications → Konfigurer email settings
- ⏳ Slack notifications → Konfigurer Slack webhook

---

## 🚀 Neste Steg

1. **Installer GeoIP (som root):**
   ```bash
   apt-get install -y geoip-bin geoip-database geoip-database-extra
   ```

2. **Konfigurer AbuseIPDB (anbefalt):**
   - Opprett konto og få API-nøkkel
   - Legg til i `.env`

3. **Konfigurer notifications (valgfritt):**
   - Email settings i `.env`
   - Slack webhook hvis ønsket

4. **Test alt:**
   - Verifiser GeoIP: `geoiplookup 8.8.8.8`
   - Sjekk dashboard: https://nytt.smartesider.no/dashboard
   - Overvåk logs: `tail -f storage/logs/laravel.log`

---

**Dokumentasjon oppdatert:** 2025-01-15  
**Implementert av:** GitHub Copilot AI  
**Widget versjon:** 2.0 (med GeoIP, Risk Score, IP Reputation, Notifications)
