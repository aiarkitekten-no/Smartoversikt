# AI Safety Guardrails & Widget Protection System

**Opprettet**: 2025-10-11  
**Formål**: Forhindre utilsiktede endringer i database, widgets og brukerdata fra AI-assistanse  
**Prioritet**: KRITISK

---

## 🚨 PROBLEM-ANALYSE

### Risiko som har oppstått:
1. ✅ **migrate:fresh kjørt på produksjon** → Alle brukerdata slettet
2. ✅ **Fake data insertet** → Måtte gjenopprette fra arkiv
3. ⚠️ **Widget-endringer medfølger cascade-feil** → Andre widgets brekker

### Potensielle fremtidige risikoer:
- AI foreslår destruktive kommandoer uten tilstrekkelig forståelse
- Endringer i én widget påvirker andre widgets utilsiktet
- Database-operasjoner overskriver produksjonsdata
- Seeders kjører og erstatter ekte brukerdata

---

## 🛡️ LAG 1: DATABASE-BESKYTTELSE

### 1.1 Production Environment Guards

**Fil**: `app/Providers/AppServiceProvider.php`

```php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

public function boot(): void
{
    // KRITISK: Blokkér farlige kommandoer i produksjon
    if (App::environment('production')) {
        DB::prohibitDestructiveCommands();
        
        // Logg alle database-operasjoner
        DB::listen(function ($query) {
            if (preg_match('/DROP|TRUNCATE|DELETE/i', $query->sql)) {
                Log::critical('🚨 DESTRUCTIVE QUERY DETECTED', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time,
                    'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
                ]);
            }
        });
    }
}
```

### 1.2 Seeder Protection

**Alle seeders MÅ ha beskyttelse mot overskrivning:**

```php
// ✅ RIKTIG - Sjekk om data finnes først
public function run(): void
{
    // GUARD: Ikke overskriv eksisterende data
    if (Widget::count() > 0) {
        $this->command->warn('⚠️  Widgets allerede eksisterer. Skipper seeding.');
        return;
    }
    
    // Eller: Bare legg til manglende
    $configWidgets = config('widgets');
    foreach ($configWidgets as $key => $widget) {
        Widget::firstOrCreate(
            ['widget_key' => $key],  // Finn basert på nøkkel
            $widget                   // Opprett med disse verdiene
        );
    }
}
```

```php
// ❌ FARLIG - Overskriver blindt
public function run(): void
{
    Widget::truncate(); // ALDRI GJØ DETTE I PRODUKSJON!
    DB::table('widgets')->insert($data);
}
```

### 1.3 Forbidden Commands List

**Fil**: `.ai-forbidden-commands`

```bash
# ALDRI KJØR DISSE KOMMANDOENE I PRODUKSJON
php artisan migrate:fresh
php artisan migrate:reset
php artisan db:wipe
php artisan migrate:rollback --step=999

# KREVER EKSPLISITT BRUKER-GODKJENNING
php artisan migrate:fresh --seed
php artisan db:seed --force
php artisan migrate:rollback
```

### 1.4 Automatic Backup Before Migrations

**Fil**: `scripts/safe-migrate.sh`

```bash
#!/bin/bash
# Sikker migrasjon med automatisk backup

echo "🔒 AI SAFETY: Creating backup before migration..."

# Backup database
BACKUP_FILE="database/backups/pre-migration-$(date +%Y%m%d-%H%M%S).sql"
mkdir -p database/backups

if [ "$DB_CONNECTION" = "mysql" ]; then
    mysqldump -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > "$BACKUP_FILE"
    echo "✅ MySQL backup created: $BACKUP_FILE"
fi

# Kjør migrasjonen
php artisan migrate "$@"

# Verifiser at viktige tabeller fortsatt eksisterer
php artisan tinker --execute="
if (DB::table('users')->count() === 0) {
    echo '🚨 CRITICAL: Users table is empty! Restore backup immediately!' . PHP_EOL;
    exit(1);
}
if (DB::table('widgets')->count() === 0) {
    echo '🚨 WARNING: Widgets table is empty!' . PHP_EOL;
}
echo '✅ Database verification passed' . PHP_EOL;
"
```

**Bruk**: `./scripts/safe-migrate.sh` i stedet for `php artisan migrate`

---

## 🔒 LAG 2: WIDGET-LÅSING

### 2.1 Widget Lock Manifest

**Fil**: `.widget-locks.json`

```json
{
  "locked_widgets": [
    "demo.clock",
    "mail.imap-inbox",
    "business.bills",
    "tools.rss-reader",
    "tools.quicklinks",
    "dev.github"
  ],
  "work_mode": "LOCKED",
  "current_widget": null,
  "locked_at": "2025-10-11T10:30:00Z",
  "locked_by": "production_guard"
}
```

### 2.2 Pre-commit Hook for Widget Protection

**Fil**: `.git/hooks/pre-commit`

```bash
#!/bin/bash
# Widget Protection Hook

LOCK_FILE=".widget-locks.json"

if [ ! -f "$LOCK_FILE" ]; then
    echo "⚠️  No widget lock file found. Creating..."
    echo '{"locked_widgets":[],"work_mode":"UNLOCKED"}' > "$LOCK_FILE"
fi

# Sjekk om låste widgets er endret
CHANGED_WIDGETS=$(git diff --cached --name-only | grep "resources/views/widgets/")

if [ -n "$CHANGED_WIDGETS" ]; then
    echo "🔍 Widget changes detected:"
    echo "$CHANGED_WIDGETS"
    
    # Les låste widgets
    LOCKED=$(jq -r '.locked_widgets[]' "$LOCK_FILE" 2>/dev/null)
    
    for widget in $CHANGED_WIDGETS; do
        widget_name=$(basename "$widget" .blade.php)
        
        # Sjekk om widget er låst
        if echo "$LOCKED" | grep -q "$widget_name"; then
            echo "🚨 ERROR: Attempting to modify LOCKED widget: $widget_name"
            echo "   Use: ./scripts/unlock-widget.sh $widget_name"
            exit 1
        fi
    done
    
    echo "✅ Widget changes allowed"
fi
```

### 2.3 Widget Lock/Unlock Scripts

**Fil**: `scripts/unlock-widget.sh`

```bash
#!/bin/bash
# Låser opp en widget for redigering

WIDGET_NAME=$1
LOCK_FILE=".widget-locks.json"

if [ -z "$WIDGET_NAME" ]; then
    echo "Usage: ./scripts/unlock-widget.sh <widget-name>"
    echo "Example: ./scripts/unlock-widget.sh season-tree-lights"
    exit 1
fi

# Oppdater lock-fil
jq --arg widget "$WIDGET_NAME" '
  .locked_widgets = (.locked_widgets - [$widget]) |
  .current_widget = $widget |
  .work_mode = "EDITING" |
  .unlocked_at = now | todateiso8601
' "$LOCK_FILE" > "${LOCK_FILE}.tmp" && mv "${LOCK_FILE}.tmp" "$LOCK_FILE"

echo "🔓 Widget unlocked: $WIDGET_NAME"
echo "   You can now safely edit: resources/views/widgets/${WIDGET_NAME}.blade.php"
echo ""
echo "   Remember to lock it again when done:"
echo "   ./scripts/lock-widget.sh $WIDGET_NAME"
```

**Fil**: `scripts/lock-widget.sh`

```bash
#!/bin/bash
# Låser en widget etter redigering

WIDGET_NAME=$1
LOCK_FILE=".widget-locks.json"

if [ -z "$WIDGET_NAME" ]; then
    echo "Usage: ./scripts/lock-widget.sh <widget-name>"
    exit 1
fi

# Legg til i låste widgets
jq --arg widget "$WIDGET_NAME" '
  .locked_widgets += [$widget] |
  .locked_widgets |= unique |
  .current_widget = null |
  .work_mode = "LOCKED" |
  .locked_at = now | todateiso8601
' "$LOCK_FILE" > "${LOCK_FILE}.tmp" && mv "${LOCK_FILE}.tmp" "$LOCK_FILE"

echo "🔒 Widget locked: $WIDGET_NAME"
```

### 2.4 Widget Integrity Verification

**Fil**: `scripts/verify-widget-integrity.sh`

```bash
#!/bin/bash
# Verifiser at alle widgets har gyldig HTML-struktur

echo "🔍 Verifying widget integrity..."

ERROR_COUNT=0

for widget in resources/views/widgets/*.blade.php; do
    widget_name=$(basename "$widget")
    
    # Sjekk at <div> tags er balansert
    open_divs=$(grep -o '<div' "$widget" | wc -l)
    close_divs=$(grep -o '</div>' "$widget" | wc -l)
    
    if [ "$open_divs" -ne "$close_divs" ]; then
        echo "❌ $widget_name: Unbalanced <div> tags ($open_divs open, $close_divs close)"
        ERROR_COUNT=$((ERROR_COUNT + 1))
    fi
    
    # Sjekk at det ikke er Math.random() (JavaScript i PHP-context)
    if grep -q 'Math\.' "$widget"; then
        echo "❌ $widget_name: Contains Math.random() - use PHP rand() instead"
        ERROR_COUNT=$((ERROR_COUNT + 1))
    fi
done

if [ $ERROR_COUNT -eq 0 ]; then
    echo "✅ All widgets passed integrity check"
    exit 0
else
    echo "🚨 $ERROR_COUNT widget(s) failed integrity check"
    exit 1
fi
```

---

## 🤖 LAG 3: AI GUARDRAILS

### 3.1 AI Instruction File

**Fil**: `.ai-instructions.md`

```markdown
# AI ASSISTANT SAFETY INSTRUCTIONS

## ⛔ ALDRI KJØR DISSE KOMMANDOENE
- `php artisan migrate:fresh` - Sletter ALL data
- `php artisan migrate:reset` - Sletter migrasjonshistorikk
- `php artisan db:wipe` - Tømmer databasen
- `DB::table()->truncate()` - Sletter tabelldata
- `DELETE FROM` uten WHERE-clause

## ✅ ALLTID GJØ DETTE FØR DATABASE-ENDRINGER
1. Sjekk `APP_ENV` - hvis `production`, STOPP
2. Kjør `./scripts/safe-migrate.sh` i stedet for `php artisan migrate`
3. Bruk `firstOrCreate()` i seeders, ALDRI `truncate() + insert()`

## 🔒 WIDGET-REDIGERING
1. Sjekk `.widget-locks.json` før endringer
2. Kjør `./scripts/unlock-widget.sh <name>` først
3. Verifiser endringer: `./scripts/verify-widget-integrity.sh`
4. Lås igjen: `./scripts/lock-widget.sh <name>`

## 📋 SJEKKLISTE FØR ENDRINGER
- [ ] Er dette produksjon? (Sjekk .env)
- [ ] Finnes det backup? (database/backups/)
- [ ] Er widget låst? (Sjekk .widget-locks.json)
- [ ] Har jeg testet lokalt først?
- [ ] Vil denne endringen påvirke andre komponenter?

## 🆘 HVIS NOEN GÅR GALT
1. STOPP umiddelbart
2. Gjenopprett fra backup: `./scripts/restore-backup.sh`
3. Rapporter til bruker med full error-log
```

### 3.2 AI Command Filter

**Fil**: `scripts/ai-command-filter.sh`

```bash
#!/bin/bash
# Filtrer farlige kommandoer før utførelse

COMMAND="$1"

# Rød flagg-sjekk
FORBIDDEN=(
    "migrate:fresh"
    "migrate:reset"
    "db:wipe"
    "truncate"
    "DROP TABLE"
    "DROP DATABASE"
)

for forbidden in "${FORBIDDEN[@]}"; do
    if echo "$COMMAND" | grep -qi "$forbidden"; then
        echo "🚨 BLOCKED: Forbidden command detected: $forbidden"
        echo "   Command: $COMMAND"
        echo ""
        echo "   This command can cause data loss."
        echo "   If you really need to run this, do it manually."
        exit 1
    fi
done

# Advarsel for destruktive operasjoner
WARNINGS=(
    "DELETE"
    "UPDATE"
    "migrate:rollback"
)

for warning in "${WARNINGS[@]}"; do
    if echo "$COMMAND" | grep -qi "$warning"; then
        echo "⚠️  WARNING: Potentially destructive command: $warning"
        read -p "   Are you sure? (yes/NO): " confirm
        if [ "$confirm" != "yes" ]; then
            echo "   Cancelled."
            exit 1
        fi
    fi
done

echo "✅ Command approved"
```

---

## 📊 LAG 4: MONITORING & LOGGING

### 4.1 Database Operation Logger

**Fil**: `app/Observers/DatabaseActivityObserver.php`

```php
<?php

namespace App\Observers;

use Illuminate\Support\Facades\Log;

class DatabaseActivityObserver
{
    public function created($model): void
    {
        $this->logActivity('CREATED', $model);
    }

    public function updated($model): void
    {
        $this->logActivity('UPDATED', $model);
    }

    public function deleted($model): void
    {
        $this->logActivity('DELETED', $model);
    }

    private function logActivity(string $action, $model): void
    {
        // Logg spesielt for kritiske modeller
        $criticalModels = ['User', 'Widget', 'UserWidget', 'Bill', 'MailAccount'];
        
        if (in_array(class_basename($model), $criticalModels)) {
            Log::channel('database')->info("🔍 {$action}", [
                'model' => get_class($model),
                'id' => $model->id ?? 'N/A',
                'changes' => $model->getDirty(),
                'user_agent' => request()->userAgent(),
                'ip' => request()->ip(),
            ]);
        }
    }
}
```

### 4.2 Daily Integrity Check (Cron)

**Fil**: `app/Console/Commands/VerifyDatabaseIntegrity.php`

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class VerifyDatabaseIntegrity extends Command
{
    protected $signature = 'db:verify-integrity';
    protected $description = 'Verify database integrity and critical data';

    public function handle(): int
    {
        $this->info('🔍 Verifying database integrity...');
        
        $checks = [
            'Users' => \App\Models\User::count(),
            'Widgets' => \App\Models\Widget::count(),
            'User Widgets' => \App\Models\UserWidget::count(),
            'Bills' => \App\Models\Bill::count(),
            'Mail Accounts' => \DB::table('mail_accounts')->count(),
        ];
        
        $failed = false;
        
        foreach ($checks as $table => $count) {
            if ($count === 0) {
                $this->error("❌ {$table}: EMPTY (CRITICAL!)");
                $failed = true;
            } else {
                $this->info("✅ {$table}: {$count} records");
            }
        }
        
        if ($failed) {
            $this->error('🚨 INTEGRITY CHECK FAILED - Restore from backup!');
            return self::FAILURE;
        }
        
        $this->info('✅ All integrity checks passed');
        return self::SUCCESS;
    }
}
```

**Legg til i `app/Console/Kernel.php`:**

```php
protected function schedule(Schedule $schedule): void
{
    // Kjør integritetsjekk hver natt
    $schedule->command('db:verify-integrity')
        ->dailyAt('03:00')
        ->emailOutputOnFailure('terje@smartesider.no');
}
```

---

## 🎯 IMPLEMENTERINGSPLAN

### Fase 1: UMIDDELBART (i dag)
1. ✅ Opprett `.widget-locks.json` med alle nåværende widgets låst
2. ✅ Opprett `.ai-forbidden-commands` liste
3. ✅ Opprett `.ai-instructions.md` for AI-assistanse
4. ✅ Installer pre-commit hook for widget-beskyttelse

### Fase 2: DENNE UKEN
1. ⏳ Implementer `safe-migrate.sh` backup-script
2. ⏳ Oppdater alle seeders til å bruke `firstOrCreate()`
3. ⏳ Legg til `DB::prohibitDestructiveCommands()` i production
4. ⏳ Opprett `db:verify-integrity` command

### Fase 3: NESTE UKE
1. ⏳ Implementer DatabaseActivityObserver
2. ⏳ Sett opp daglig integrity check
3. ⏳ Opprett restore-backup script
4. ⏳ Dokumenter rollback-prosedyre

---

## 📖 BRUKSANVISNING

### Når du skal redigere en widget:

```bash
# 1. Lås opp widget
./scripts/unlock-widget.sh season-tree-lights

# 2. Gjør endringer
# ... edit resources/views/widgets/season-tree-lights.blade.php ...

# 3. Verifiser integritet
./scripts/verify-widget-integrity.sh

# 4. Test i nettleser
php artisan view:clear

# 5. Lås widget igjen
./scripts/lock-widget.sh season-tree-lights
```

### Når du skal kjøre migrations:

```bash
# ❌ ALDRI:
php artisan migrate:fresh

# ✅ ALLTID:
./scripts/safe-migrate.sh
```

### Når du skal seede data:

```bash
# ❌ FARLIG:
Widget::truncate();
Widget::insert($data);

# ✅ TRYGT:
foreach ($data as $key => $widget) {
    Widget::firstOrCreate(['widget_key' => $key], $widget);
}
```

---

## 🚨 HVAOMHVISKER

### "Jeg slettet ved et uhell produksjonsdata!"

```bash
# 1. Finn siste backup
ls -lt database/backups/

# 2. Restore
./scripts/restore-backup.sh database/backups/pre-migration-20251011-103000.sql

# 3. Verifiser
php artisan db:verify-integrity
```

### "En widget er låst, men jeg MÅ endre den!"

```bash
# Lås opp midlertidig
./scripts/unlock-widget.sh <widget-name>

# Gjør endringer

# LÅS IGJEN UMIDDELBART
./scripts/lock-widget.sh <widget-name>
```

### "AI foreslo en farlig kommando!"

**IKKE KJØR DEN!** AI er en assistent, ikke en beslutningstaker.

Vurder alltid:
- Vil dette slette data?
- Kan dette påvirke produksjon?
- Finnes det en tryggere måte?

---

**Dette dokumentet er et levende dokument. Oppdater det når nye risikoscenarier oppdages.**
