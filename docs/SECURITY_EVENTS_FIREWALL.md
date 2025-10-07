# Security Events - Firewall Blocking Setup

## 🚨 Kritiske Endringer

### ✅ Refresh-intervall oppdatert
- **Før:** 120 sekunder (2 minutter)
- **Nå:** 30 sekunder
- **Årsak:** Raskere respons ved aktive angrep

### 🛡️ Ny Funksjonalitet: IP-blokkering

Widgeten har nå mulighet til å blokkere mistenkelige IP-adresser direkte i firewall.

## 🔧 Oppsett Påkrevd

For å aktivere IP-blokkering må du kjøre setup-scriptet som **root**:

```bash
sudo /var/www/vhosts/smartesider.no/nytt.smartesider.no/scripts/setup-firewall-permissions.sh
```

Dette scriptet:
1. ✅ Detekterer webserver-bruker (www-data/nginx)
2. ✅ Oppretter `/etc/sudoers.d/skydash-firewall`
3. ✅ Gir tillatelse til:
   - `iptables` for IP-blokkering
   - `fail2ban-client` for persistent blokkering
   - `at` for automatisk opphevelse etter 2 timer
4. ✅ Oppretter `DASHBOARD_BLOCKS` iptables chain

## 🎯 Hvordan det Fungerer

### Når et kritisk angrep oppdages:

1. **Widgeten viser hendelsen** med 🔴 kritisk-merking
2. **"Blokker i Firewall (2t)"-knapp** vises
3. **Ved klikk:**
   - Bekreftelsesdialog vises
   - IP blokkeres i iptables
   - IP legges til fail2ban jail
   - Automatisk opphevelse planlegges etter 2 timer
   - Hendelse logges i Laravel log

### Blokkering skjer i 3 lag:

1. **iptables (midlertidig)**
   ```bash
   iptables -I DASHBOARD_BLOCKS -s <IP> -j DROP
   ```
   - Varighet: 2 timer
   - Auto-opphevelse via `at` kommando

2. **fail2ban (persistent)**
   ```bash
   fail2ban-client set recidive banip <IP>
   ```
   - Varighet: Til manuell opphevelse
   - Legges til recidive eller sshd jail

3. **Logging**
   - Logg i Laravel: `storage/logs/laravel.log`
   - Inneholder: IP, årsak, bruker, tidspunkt

## 🔒 Sikkerhet

### Beskyttelse mot feil blokkering:

✅ **Kan IKKE blokkere:**
- `127.0.0.0/8` (localhost)
- `10.0.0.0/8` (private)
- `172.16.0.0/12` (private)
- `192.168.0.0/16` (private)
- `::1` (IPv6 localhost)
- `fc00::/7` (IPv6 private)

✅ **Validering:**
- IP-adresse må være gyldig
- Kun brukere med dashboard-tilgang kan blokkere
- Bekreftelsesdialog før blokkering
- Alle aksjoner logges

## 📊 UI-endringer

### Blokkeringsknapp vises kun for:
- ✅ `severity === 'critical'` (ANGREP)
- ✅ `type === 'suspicious_request'` (SQL injection, XSS, Path traversal)

### Knapp-tilstander:
- **Normal:** 🚫 Blokker i Firewall (2t)
- **Loading:** ⏳ Blokkerer...
- **Disabled:** Kun én IP om gangen

## 🧪 Testing

```bash
# Test refresh-intervall
cd /var/www/vhosts/smartesider.no/nytt.smartesider.no
php artisan widgets:refresh security.events --force

# Se logging
tail -f storage/logs/laravel.log

# Sjekk iptables chain
sudo iptables -L DASHBOARD_BLOCKS -n -v

# Sjekk fail2ban status
sudo fail2ban-client status recidive
```

## ⚠️ Viktige Notater

1. **Root-tilgang påkrevd** for første gangs oppsett
2. **Webserver må ha sudo-rettigheter** (via sudoers.d)
3. **at-demon må kjøre** for auto-opphevelse
4. **fail2ban må være installert** for persistent blokkering
5. **Blokkerte IPer fjernes automatisk** etter 2 timer fra iptables
6. **fail2ban-blokkering** må fjernes manuelt hvis ønskelig

## 🚀 API Endpoint

```
POST /api/security/block-ip
```

**Request:**
```json
{
  "ip": "192.0.2.1",
  "reason": "SQL injection attempt"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "IP 192.0.2.1 er blokkert i 2 timer",
  "blocked_ip": "192.0.2.1",
  "duration": "2 timer",
  "unblock_time": "2025-10-07T13:00:00+02:00"
}
```

## 📝 Changelog

### 2025-10-07
- ✅ Refresh-intervall endret fra 120s til 30s
- ✅ Lagt til IP-blokkering i firewall (2 timer)
- ✅ Lagt til fail2ban-integrasjon
- ✅ Lagt til sikkerhetsfiltrer for private IPer
- ✅ Lagt til logging av alle blokkeringer
- ✅ Auto-opphevelse via at-kommando
