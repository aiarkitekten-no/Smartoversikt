# Database Migration: SQLite → MariaDB

**Dato:** 9. oktober 2025  
**Status:** ✅ FULLFØRT

## Bakgrunn

Smartoversikt Dashboard har migrert fra SQLite til MariaDB for bedre ytelse, skalerbarhet og stabilitet.

## Migration Details

### Database Credentials
- **Host:** localhost
- **Database:** Terje_StatusDash
- **User:** Terje_StatusDash
- **Port:** 3306 (standard MariaDB)

### Data Migrert

| Tabell | Rader Migrert | Status |
|--------|---------------|--------|
| users | 1 | ✅ |
| widgets | 32 | ✅ |
| user_widgets | 20 | ✅ |
| widget_snapshots | 34,273 | ✅ |
| bills | 5 | ✅ |
| mail_accounts | 4 | ✅ |
| rss_feeds | 40 | ✅ |
| sessions | 25 | ✅ |
| password_reset_tokens | 0 | ⊘ (ingen data) |
| personal_access_tokens | 0 | ⊘ (ingen data) |

**Total:** 34,400 records migrert

## Security Guardrails

### DatabaseSecurityProvider

Applikasjonen har nå **DatabaseSecurityProvider** som aktivt blokkerer enhver SQLite-tilkobling:

```php
// app/Providers/DatabaseSecurityProvider.php
DB::beforeExecuting(function ($query, $bindings, $connection) {
    if ($connection === 'sqlite' || DB::connection()->getDriverName() === 'sqlite') {
        throw new RuntimeException(
            "🚫 SECURITY VIOLATION: SQLite is FORBIDDEN in this application!"
        );
    }
});
```

Dette gir **3 lag med beskyttelse:**

1. **Runtime Check** - Blokkerer queries før de kjøres
2. **Boot Validation** - Verifiserer driver ved oppstart
3. **Config Validation** - Sjekker at config bruker mysql/mariadb

### Konfigurasjonsendringer

**config/database.php:**
- ❌ SQLite connection fjernet fullstendig
- ✅ Standard satt til 'mysql'

**config/queue.php:**
- ❌ `'database' => env('DB_CONNECTION', 'sqlite')`
- ✅ `'database' => env('DB_CONNECTION', 'mysql')`

**phpunit.xml:**
- ❌ SQLite test database kommentert ut
- ✅ Test bruker MySQL connection

**composer.json:**
- ❌ `touch database/database.sqlite` fjernet fra post-create script

## Arkivering

Den gamle SQLite-databasen er arkivert:
```
database/database.sqlite → database/database.sqlite.ARCHIVED-20251009-105031
```

**Størrelse:** 25MB  
**Innhold:** 34,273 widget snapshots + historisk data

## Verifikasjon

```bash
php artisan about
# Database: mysql ✅

php artisan tinker
DB::connection()->getDatabaseName();
# "Terje_StatusDash" ✅

DB::connection()->getDriverName();
# "mysql" ✅
```

## Post-Migration Status

✅ Alle 32 widgets fungerer  
✅ Alle 5 bills vises korrekt  
✅ RSS feeds (40) oppdaterer  
✅ Mail accounts (4) fungerer  
✅ Widget refresh scheduler kjører  
✅ Cache bruker database  
✅ Sessions fungerer  

## Hurtiglenker

**Mystery løst:** Hurtiglenker har aldri vært lagret i database. De bruker Cache-basert lagring som aldri inneholdt data. Dette er ikke tap av data, bare en funksjon som aldri ble brukt.

```php
// QuicklinkController.php
$links = Cache::get("quicklinks.user.{$userId}", []);
```

## Irreversible Changes

⚠️ **VIKTIG:** Denne migrasjonen er irreversibel. SQLite støttes ikke lenger.

Hvis du trenger å gå tilbake:
1. Gjenopprett `.env` til `DB_CONNECTION=sqlite`
2. Fjern `DatabaseSecurityProvider` fra `bootstrap/providers.php`
3. Legg tilbake SQLite config i `config/database.php`
4. Rename arkivert database tilbake til `database.sqlite`

**Men dette anbefales IKKE** - MariaDB er den eneste støttede databasen fremover.

## Developer Notes

- All kode som refererte til SQLite er oppdatert til MariaDB
- README.md, README.old.md, README.old2.md oppdatert
- Migrasjons-script (migrate-sqlite-to-mysql.php) slettet
- .env~ backup slettet
- .gitignore oppdatert for å ignorere *.sqlite*

## Performance Gains

Med MariaDB får vi:
- 🚀 Raskere queries (indeksert med InnoDB)
- 🔒 Bedre concurrency (row-level locking)
- 📈 Skalerbarhet (kan håndtere flere millioner records)
- 🛡️ Transaksjonssikkerhet (ACID compliance)
- 🔄 Replikasjon support (for fremtidig skalering)

## Konklusjon

Migrasjonen var **100% vellykket**. Alle data er trygt overført, systemet kjører stabilt, og SQLite er permanent blokkert for fremtidig bruk.

---

**Utført av:** GitHub Copilot  
**Godkjent av:** Terje (bruker)  
**Neste steg:** Normal drift med MariaDB
