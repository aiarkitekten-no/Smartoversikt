# 🐙 GitHub Widget Feilsøking

## Steg-for-steg test

### 1. Verifiser at settings lagres
1. Klikk på ⚙️ på GitHub widget
2. Fyll inn:
   - Username: `ditt-brukernavn`
   - Token: `ghp_xxxxxxxxxxxx`
3. Klikk "Lagre"
4. Åpne browser console (F12)
5. Se etter:
   ```
   Saving settings for widget: <ID>
   Payload: { "settings": { "username": "...", "token": "..." } }
   Response status: 200
   ```

### 2. Test GitHub API direkte
Besøk (bytt ut verdier):
```
https://nytt.smartesider.no/test-github/DITT-BRUKERNAVN/ghp_DINTOKEN
```

Dette vil vise:
- User data fra GitHub
- Events data
- Eventuelle feilmeldinger

### 3. Sjekk Laravel logs
```bash
tail -f storage/logs/laravel.log
```

Se etter:
```
Fetching GitHub data for user: DITT-BRUKERNAVN
GitHub user fetched: DITT-BRUKERNAVN
GitHub events count: XX
```

### 4. Refresh dashboard
1. Gå tilbake til dashboard
2. Hard refresh (Ctrl+Shift+R)
3. Widget skal nå vise dine data

## Vanlige problemer

### Token format feil
GitHub Personal Access Tokens starter med:
- Classic tokens: `ghp_`
- Fine-grained: `github_pat_`

Verifiser at du bruker riktig format.

### Mangler permissions
Token trenger:
- `read:user` (minimum)
- `repo` (for private repos)

### Rate limiting
GitHub API har limits:
- Uautentisert: 60 requests/time
- Autentisert: 5000 requests/time

### Cache problemer
Widget data caches. For å force refresh:
```bash
php artisan cache:clear
```

## Debug output

Hvis widget fortsatt viser mock data, sjekk:

1. **Er username 'octocat'?**
   - Hvis ja: Settings ikke lastet korrekt

2. **Er token tom?**
   - Hvis ja: Token ikke lagret

3. **Får du "GitHub API fetch failed"?**
   - Sjekk token permissions
   - Sjekk om token er utløpt

## Test med curl

```bash
curl -H "Authorization: Bearer ghp_DINTOKEN" \
     -H "Accept: application/vnd.github.v3+json" \
     -H "User-Agent: Smartesider-Dashboard" \
     https://api.github.com/users/DITT-BRUKERNAVN
```

Skal returnere JSON med brukerdata.

## Neste steg

Hvis alt over fungerer men widget fortsatt viser mock data:
1. Clear all caches
2. Restart PHP-FPM
3. Hard refresh browser
4. Sjekk at `getData()` faktisk kalles i dashboard.blade.php
