# Widget Konfigurasjon 🎛️

## Oversikt
Følgende widgets krever konfigurasjon før de kan brukes korrekt:

---

## 🌐 Website Uptime Monitor

Overvåk oppetid og responstid for flere nettsider samtidig.

### Konfigurasjon
1. Klikk på ⚙️ (innstillinger) på widget
2. Legg til nettsider:
   - **Klikk "+ Legg til nettside"**
   - Fyll inn navn (f.eks. "Smartesider")
   - Fyll inn URL (f.eks. "https://smartesider.no")
   - Gjenta for hver nettside du vil overvåke
3. Sett globale innstillinger:
   - **Sjekk-intervall**: Hvor ofte alle nettsider skal sjekkes (30 sek - 10 min)
   - **Timeout**: Hvor lenge skal vi vente på svar (1-30 sekunder)
4. Klikk "Lagre"

### Eksempel Konfigurasjon
```json
{
  "websites": [
    { "name": "Smartesider", "url": "https://smartesider.no" },
    { "name": "VG", "url": "https://www.vg.no" },
    { "name": "NRK", "url": "https://www.nrk.no" },
    { "name": "Min Server", "url": "http://192.168.1.100" }
  ],
  "check_interval": "60",
  "timeout": 5
}
```

### Hvordan det fungerer
- Sender HTTP GET request til hver URL hver X sekund
- Tracker responstid og status-kode per nettside
- Beregner 24-timers uptime % basert på historikk
- Visuell status:
  - ✅ Grønn = Alle oppe
  - ⚠️ Gul = Noen nede
  - 🚨 Rød = Alle nede

### Widget Display
- **1 nettside**: Viser hostname (f.eks. "smartesider.no")
- **Flere nettsider**: Viser antall (f.eks. "4 nettsider")
- **Liste**: Hver nettside vises med status, responstid og uptime %

### Fjerne nettsider
Klikk på ✕ ved siden av nettsiden i settings-dialog.

---

## 🐙 GitHub Activity

Vis din GitHub aktivitet med commits, PRs og issues.

### Konfigurasjon
1. Klikk på ⚙️ (innstillinger) på widget
2. Fyll inn:
   - **GitHub Brukernavn**: Ditt GitHub brukernavn (f.eks. `octocat`)
   - **Personal Access Token**: GitHub token for API tilgang
   - **Vis private repositories**: Huk av for å inkludere private repos

### Hvordan få GitHub Token
1. Gå til GitHub: [Settings → Developer settings → Personal access tokens](https://github.com/settings/tokens)
2. Klikk "Generate new token (classic)"
3. Velg scopes:
   - `repo` (for private repos)
   - `read:user`
   - `read:org` (hvis du vil se org activity)
4. Generer og kopier token
5. Lim inn i widget settings

### Eksempel Konfigurasjon
```json
{
  "username": "torvalds",
  "token": "ghp_xxxxxxxxxxxxxxxxxxxx",
  "show_private": false
}
```

### Hvordan det fungerer
- Henter aktivitet fra GitHub API v3
- Viser dagens commits, lines added/deleted
- Tracker PRs og issues
- Real-time data fra GitHub Events API
- Fallback til mock data hvis token mangler

### Sikkerhet
⚠️ **VIKTIG**: Token lagres kryptert i databasen. Bruk kun tokens med minimal tilgang (read-only).

---

## 💰 Stripe Dashboard

Vis salg og transaksjoner fra Stripe (kommer snart).

### Status
🚧 Bruker for øvrig mock data. Real API integrasjon kommer i fremtidig versjon.

---

## Generelle Widget Settings

### Oppdateringsinterval
Alle widgets kan konfigureres med eget refresh-interval:
- Minimum: 10 sekunder
- Maksimum: 3600 sekunder (1 time)
- La stå tom for standard intervall

### Tilgang til Settings
- Klikk på ⚙️ ikonet på en widget
- Settings modal åpnes
- Gjør endringer
- Klikk "Lagre"
- Widget vil refreshe med nye settings

---

## Teknisk Dokumentasjon

### Hvordan Settings Fungerer

Settings lagres i `user_widgets.settings` JSON-kolonne:
```sql
user_widgets (
  id,
  user_id,
  widget_id,
  settings JSON,  -- Her lagres konfigurasjon
  refresh_interval,
  position,
  ...
)
```

### Fetcher Access
Fetchere har tilgang til settings via `$this->userWidget->settings`:

```php
class DevGithubFetcher extends BaseWidgetFetcher
{
    protected function fetchData(): array
    {
        $username = $this->userWidget->settings['username'] ?? 'octocat';
        $token = $this->userWidget->settings['token'] ?? null;
        
        // Fetch from GitHub API...
    }
}
```

### View Access
Views har tilgang via `$userWidget` variabel:

```blade
@php
    $username = $userWidget->settings['username'] ?? 'octocat';
@endphp

<span>@{{ $username }}</span>
```

### Definere Settings i Config
```php
// config/widgets.php
'dev.github' => [
    'name' => 'GitHub Activity',
    'settings' => [
        'username' => [
            'type' => 'text',
            'label' => 'GitHub Brukernavn',
            'required' => true,
        ],
        'token' => [
            'type' => 'password',
            'label' => 'Personal Access Token',
            'required' => true,
        ],
    ],
],
```

---

## Feilsøking

### Widget viser "octocat" i stedet for mitt brukernavn
- Sjekk at du har lagret settings
- Refresh siden
- Sjekk browser console for errors

### GitHub widget viser mock data
- Sjekk at token er korrekt
- Verifiser at token har riktige permissions
- Se server logs: `tail -f storage/logs/laravel.log`

### Uptime widget viser "Unknown"
- Sjekk at URL er korrekt (må inkludere `http://` eller `https://`)
- Test URL i browser først
- Sjekk timeout settings (øk hvis nettside er treg)

---

## Fremtidige Forbedringer

- [ ] Mulighet for å overvåke flere nettsider i én widget
- [ ] GitHub: Graph over commits per dag
- [ ] Stripe: Real API integration
- [ ] Slack notifications ved downtime
- [ ] Custom alerting rules

---

**Spørsmål eller problemer?** Se `docs/` eller kontakt support.
