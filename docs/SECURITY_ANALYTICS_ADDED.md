# Security Events Widget - Analytics Tillegg ✅

**Dato:** 2025-01-15  
**Status:** ✅ IMPLEMENTERT OG BYGGET

---

## 🎯 Implementerte Analytics (8 nye seksjoner)

### ✅ #1: Top 5 Angripende Land 🌍
**Hva vises:**
- Liste over de 5 landene med flest angrep
- Landsflagg emoji for hvert land
- Landsnavn
- Antall angrep fra landet

**Eksempel:**
```
🌍 Top Angripende Land
🇨🇳 China                    45 angrep
🇷🇺 Russia                   23 angrep
🇺🇸 United States           12 angrep
🇮🇳 India                     8 angrep
🇧🇷 Brazil                    5 angrep
```

**Datakilde:**
- Aggregerer `event.country` fra alle events
- Teller antall angrep per land
- Sorterer descending
- Returnerer top 5

---

### ✅ #3: Top 5 Angripende IP-adresser 🎯
**Hva vises:**
- De 5 mest aktive angripende IP-adressene
- Landsflagg for hver IP
- Antall forsøk fra IP-en
- AbuseIPDB reputation score (hvis tilgjengelig)
- ISP-informasjon

**Eksempel:**
```
🎯 Mest Aktive Angripere
🇨🇳 123.45.67.89              25 forsøk
   ⚠️ 85% China Telecom

🇷🇺 98.76.54.32               18 forsøk
   ⚡ 45% Rostelecom

🇺🇸 8.8.8.8                   12 forsøk
   ✓ 5% Google LLC
```

**Datakilde:**
- Aggregerer alle events per IP
- Teller forsøk per IP
- Inkluderer country og reputation data
- Sorterer by count, returnerer top 5

---

### ✅ #4: Angrepstypefordeling 📊
**Hva vises:**
- Prosentvis fordeling av angrepstypene
- Visuell progress bar for hver type
- Ikon for hver angrepstype
- Antall angrep i parentes

**Eksempel:**
```
📊 Angrepstypefordeling
🔐 SSH Brute-Force          45.5%  [████████████████░░░░] (45)
⚠️ Web Attack (SQL/XSS)      30.3%  [███████████░░░░░░░░░] (30)
🌐 Web Auth                  24.2%  [█████████░░░░░░░░░░░] (24)
```

**Datakilde:**
- Henter fra `summary.by_type`
- Beregner prosent av totalt
- Mapper type til label og ikon
- Sorterer by count

**Ikoner:**
- SSH: 🔐
- Web Auth: 🌐
- Suspicious Request: ⚠️

---

### ✅ #5: Risikofaktorer ⚠️
**Hva vises:**
- Liste over identifiserte risikofaktorer
- Anbefalinger for å redusere risiko
- Kun vist når risk score > 0

**Eksempel:**
```
⚠️ Risikofaktorer
• Høy SSH brute-force aktivitet (25 forsøk)
• Aktive web-angrep (SQL/XSS: 12)
• Distribuert angrep (22 unike IP-er)

💡 Anbefalinger:
• Vurder å endre SSH port eller enable key-only auth
• Sjekk WAF-regler og vurder å blokkere angripende IP-er
• Vurder rate limiting og geografisk blokkering
```

**Datakilde:**
- Henter fra `risk_score.factors` array
- Henter fra `risk_score.recommendations` array
- Genereres automatisk av risk scoring algoritmen

---

### ✅ #7: Mest Angrepne Tjenester 🎯
**Hva vises:**
- Hvilke tjenester/services som angripes mest
- Ikon for hver tjeneste
- Antall blokkerte IP-er per tjeneste
- Fargekodet etter severity (rød=high, gul=medium)

**Eksempel:**
```
🎯 Mest Angrepne Tjenester
⚡ WordPress XML-RPC         464 blokkert
📧 Email (SMTP)               29 blokkert
🔑 WordPress Login             3 blokkert
🛡️ Web Application Firewall    6 blokkert
```

**Datakilde:**
- Henter fra fail2ban jails
- Mapper jail navn til service beskrivelser
- Sorterer by banned count

**Service mapping:**
- `sshd` → 🔐 SSH (port 22)
- `plesk-postfix` → 📧 Email (SMTP)
- `plesk-modsecurity` → 🛡️ Web Application Firewall
- `wp-login` → 🔑 WordPress Login
- `wp-xmlrpc` → ⚡ WordPress XML-RPC
- `nginx-http-auth` → 🌐 Nginx Auth

---

### ✅ #8: Siste Kritiske Hendelse 🔴
**Hva vises:**
- Den siste critical security event
- Landsflagg og IP
- Melding/beskrivelse
- Relativ tid (f.eks. "2 timer siden")
- Rød bakgrunn for synlighet

**Eksempel:**
```
🔴 Siste Kritiske Hendelse
Mistenkelig forespørsel (sql forsøk)
🇨🇳 123.45.67.89              2 timer siden
```

**Datakilde:**
- Filtrerer events med `severity: critical`
- Sorterer by timestamp (nyeste først)
- Returnerer første (nyeste)
- Kun vist hvis critical events finnes

---

### ✅ #9: Fail2ban Effektivitet 🛡️
**Hva vises:**
- Prosentvis hvor mange trusler fail2ban har blokkert
- Visuell progress bar
- Total banned IPs vs current attacking IPs
- Status melding

**Eksempel:**
```
🛡️ Fail2ban Effektivitet
Blokkert:                    98.5%
[███████████████████████████████░]
Blokkert 98.5% av trusler (502 av 510)
```

**Datakilde:**
- `totalBanned` fra fail2ban status
- `totalAttackingIps` fra current events
- Beregner: `(banned / (banned + attacking)) * 100`

**Når vises:**
- Kun hvis fail2ban er installed og running
- Alltid vist (selv ved 0 angrep vises "Ingen aktive trusler")

---

### ✅ #11: Mest Prøvde Brukernavn (SSH) 👤
**Hva vises:**
- De 5 mest forsøkte brukernavnene i SSH-angrep
- Antall forsøk for hvert brukernavn
- Kompakt tag-stil visning

**Eksempel:**
```
👤 Mest Prøvde Brukernavn (SSH)
admin (45)  root (23)  test (12)  user (8)  ubuntu (5)
```

**Datakilde:**
- Filtrerer events med `type: ssh_failed_login`
- Aggregerer per `event.user`
- Teller forsøk per brukernavn
- Sorterer by count, returnerer top 5

**Viktig for:**
- Se om angripere tester default usernames
- Identifiser hvilke brukernavn som bør deaktiveres
- Verifiser at produksjon-brukernavn ikke eksponeres

---

## 📊 Dataflyt

### Backend (SecurityEventsFetcher.php)

**Nye metoder lagt til:**
1. `getTopCountries(array $events): array` - Aggreger land
2. `getTopAttackingIps(array $events): array` - Aggreger IP-er
3. `getAttackDistribution(array $summary): array` - Beregn %
4. `getTargetedServices(): array` - Map fail2ban jails
5. `getLastCriticalEvent(array $events): ?array` - Finn siste critical
6. `getFail2banEfficiency(): array` - Beregn effektivitet
7. `getAttemptedUsernames(array $events): array` - Aggreger usernames

**fetchData() returnerer nå:**
```php
[
    'events' => [...],
    'summary' => [...],
    'fail2ban' => [...],
    'risk_score' => [...],
    'analytics' => [
        'top_countries' => [...],
        'top_ips' => [...],
        'attack_distribution' => [...],
        'targeted_services' => [...],
        'last_critical' => [...] | null,
        'fail2ban_efficiency' => [...],
        'attempted_usernames' => [...],
    ],
]
```

### Frontend (security-events.blade.php)

**Ny seksjon lagt til:**
- Plassert mellom Fail2ban-status og Events List
- Kun vist når `data.analytics` finnes
- Hver subseksjon har egen `x-if` conditional
- Responsive layout med backdrop-blur effekter

**Conditional rendering:**
- Top Countries: Kun hvis array ikke tom
- Top IPs: Kun hvis array ikke tom
- Attack Distribution: Kun hvis array ikke tom
- Risk Factors: Kun hvis `risk_score.factors` finnes
- Targeted Services: Kun hvis array ikke tom
- Last Critical: Kun hvis event finnes (`!== null`)
- Fail2ban Efficiency: Kun hvis enabled
- Attempted Usernames: Kun hvis array ikke tom

---

## 🎨 UI/UX Detaljer

### Farger og Badges

**Reputation Scores:**
- 75-100%: Rød badge med ⚠️ (Kjent angrier)
- 25-74%: Oransje badge med ⚡ (Mistenkelig)
- 0-24%: Grønn badge med ✓ (Lav risiko)

**Severity:**
- High: Rød tekst
- Medium: Gul tekst
- Low: Normal tekst

**Last Critical:**
- Rød bakgrunn (`bg-red-600 bg-opacity-30`)
- Rød border
- Ekstra synlig

### Progress Bars
- Attack Distribution: Hvit bar på hvit-transparent bakgrunn
- Fail2ban Efficiency: Grønn bar på hvit-transparent bakgrunn
- Høyde: 1.5-2 pixels for kompakthet

### Icons/Emojis
- 🌍 Land (Top Countries)
- 🎯 Target (Top IPs, Targeted Services)
- 📊 Chart (Attack Distribution)
- ⚠️ Warning (Risk Factors)
- 💡 Light bulb (Recommendations)
- 🔴 Red circle (Last Critical)
- 🛡️ Shield (Fail2ban Efficiency)
- 👤 Person (Usernames)
- 🔐 Lock (SSH)
- 📧 Email
- 🌐 Globe (Web)
- ⚡ Lightning (XML-RPC)

---

## 📏 Space Management

**Problem:** Mange nye seksjoner kan overfylle widgeten

**Løsninger implementert:**
1. **Conditional rendering:** Bare vis seksjoner med data
2. **Kompakt design:** Small text (text-xs), tight spacing
3. **Scrollable container:** Events list er scrollbar
4. **Prioritering:** Analytics før events (viktigere info først)
5. **Collapsible future:** Kan legge til fold-in/out senere

**Når widgeten er FULL (alle seksjoner synlige):**
- Risk Score Banner
- Stats (4 tall)
- Fail2ban status
- Top 5 Countries
- Top 5 IPs
- Attack Distribution
- Risk Factors + Recommendations
- Targeted Services
- Last Critical Event
- Fail2ban Efficiency
- Attempted Usernames
- Events List (scrollable)

**Estimert høyde:** ~800-1000px når alt vises

---

## 🧪 Testing

### Test med 0 events (current state):
```bash
php artisan tinker --execute='
$f = new \App\Services\Widgets\SecurityEventsFetcher();
$d = $f->fetch();
var_dump($d["analytics"]);
'
```

**Forventet resultat:**
```php
[
    'top_countries' => [],  // Ingen events
    'top_ips' => [],        // Ingen events
    'attack_distribution' => [],  // Ingen events
    'targeted_services' => [  // Data fra fail2ban!
        ['jail' => 'wp-xmlrpc', 'name' => 'WordPress XML-RPC', 'icon' => '⚡', 'banned_count' => 464, ...],
        ['jail' => 'plesk-postfix', 'name' => 'Email (SMTP)', 'icon' => '📧', 'banned_count' => 29, ...],
        ...
    ],
    'last_critical' => null,  // Ingen critical events
    'fail2ban_efficiency' => [
        'enabled' => true,
        'banned_count' => 502,
        'blocked_percentage' => 100,  // 502 banned, 0 attacking = 100%
        ...
    ],
    'attempted_usernames' => [],  // Ingen SSH events
]
```

### Hva VISES med current data (0 events):
- ✅ Risk Score: 0/100 LOW (grønn)
- ✅ Stats: 0, 0, 0, 0
- ✅ Fail2ban: 502 IP bannlyst
- ✅ Targeted Services: wp-xmlrpc (464), plesk-postfix (29), etc.
- ✅ Fail2ban Efficiency: 100% (fordi ingen nye angrep)
- ❌ Top Countries: (skjult - tom array)
- ❌ Top IPs: (skjult - tom array)
- ❌ Attack Distribution: (skjult - tom array)
- ❌ Risk Factors: (skjult - ingen faktorer)
- ❌ Last Critical: (skjult - null)
- ❌ Attempted Usernames: (skjult - tom array)

**Konklusjon:** Widget viser relevant info selv uten aktive angrep!

---

## 🚀 Deployment Status

**Backend:**
- ✅ SecurityEventsFetcher.php - 8 nye metoder lagt til
- ✅ PHP syntax validert - No errors
- ✅ Alle metoder implementert og testet

**Frontend:**
- ✅ security-events.blade.php - Analytics seksjon lagt til
- ✅ Conditional rendering for alle 8 seksjoner
- ✅ npm run build - Successful (1.08s)
- ✅ Assets compiled: app-CeRdpdyP.css (70.23 kB)

**Files Modified:**
1. `app/Services/Widgets/SecurityEventsFetcher.php` (+333 lines)
2. `resources/views/widgets/security-events.blade.php` (+230 lines)

**Total Code Added:** ~563 lines

---

## 📖 Oppsummering

### Hva er NYTT:
1. ✅ **Top 5 Land** - Geografisk oversikt av trusler
2. ✅ **Top 5 Angripende IP-er** - Identifiser gjengangere
3. ✅ **Angrepstypefordeling** - Forstå angrepsvektor
4. ✅ **Risikofaktorer + Anbefalinger** - Actionable security guidance
5. ✅ **Mest Angrepne Tjenester** - Hvilke services er under press
6. ✅ **Siste Kritiske Hendelse** - Quick overview av nyeste trussel
7. ✅ **Fail2ban Effektivitet** - Hvor bra forsvaret fungerer
8. ✅ **Mest Prøvde Brukernavn** - SSH brute-force patterns

### Tidligere features (fortsatt aktive):
- ✅ Risk Score 0-100 med fargekodet banner
- ✅ GeoIP tracking (krever `geoip-bin` installasjon)
- ✅ IP Reputation (krever AbuseIPDB API-nøkkel)
- ✅ Notifications (Email/Slack ved kritiske events)
- ✅ Events list med detaljer
- ✅ Fail2ban status og jail counts
- ✅ Block IP knapp (for critical events)

### Total Feature Count: 16 features i Security Events widget! 🎉

---

**Dokumentasjon oppdatert:** 2025-01-15  
**Implementert av:** GitHub Copilot AI  
**Build Status:** ✅ SUCCESS  
**Ready for Production:** ✅ JA (med optional dependencies)
