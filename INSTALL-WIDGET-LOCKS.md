# 🔒 PER-WIDGET LOCK SYSTEM - INSTALLASJONSINSTRUKSJONER

## ✅ HVA ER GJORT

1. ✅ Redesignet lock-system til per-widget locks
2. ✅ Opprettet 4 nye scripts:
   - `scripts/unlock-widget.sh` - Lås opp én widget
   - `scripts/lock-widget.sh` - Lås én widget
   - `scripts/lock-all-widgets.sh` - Lås alle widgets
   - `scripts/widget-status.sh` - Vis status
3. ✅ Låst alle 40 widgets med individuelle .lock filer
4. ✅ Oppdatert `.githooks/pre-commit` med per-widget lock-sjekk
5. ✅ Opprettet `.gitignore` for å ignorere .lock filer
6. ✅ Komplett dokumentasjon

## ⏳ MANUELL INSTALLASJON KREVES

### 1. Installer Pre-commit Hook (KREVER ROOT)

Filen `.git/hooks/pre-commit` eies av root og må oppdateres manuelt:

```bash
# Som root eller med sudo:
sudo cp .githooks/pre-commit .git/hooks/pre-commit
sudo chmod +x .git/hooks/pre-commit

# Eller rediger .git/hooks/pre-commit direkte og erstatt innholdet
# med innholdet fra .githooks/pre-commit
```

**Verifiser at hooken fungerer:**
```bash
# Lås opp en widget
./scripts/unlock-widget.sh season-tree-lights

# Gjør en endring
echo "<!-- test -->" >> resources/views/widgets/season-tree-lights.blade.php

# Prøv å committe (skal fungere siden widget er ulåst)
git add resources/views/widgets/season-tree-lights.blade.php
git commit -m "Test" --dry-run

# Lås widget igjen
./scripts/lock-widget.sh season-tree-lights "Test"

# Prøv å committe igjen (skal blokkeres)
git commit -m "Test" --dry-run
# Skal gi: ❌ Commit blocked due to locked widget modifications

# Cleanup
git reset HEAD resources/views/widgets/season-tree-lights.blade.php
git checkout -- resources/views/widgets/season-tree-lights.blade.php
```

### 2. Test Systemet

```bash
# Sjekk status
./scripts/widget-status.sh

# Skal vise:
# 🔒 Locked widgets: 40
# 🔓 Unlocked widgets: 0
# ✅ Protection: 100% (Excellent!)
```

## 🎯 HVORDAN SYSTEMET FUNGERER

### Per-Widget Lock Filer

Før (gammel versjon):
```
.widget-locks.json
└── { "locked_widgets": ["widget1", "widget2", ...] }
```

**Problem**: Når du låser opp én widget, risikerer du å endre andre.

Nå (ny versjon):
```
resources/views/widgets/
├── season-tree-lights.blade.php
├── .season-tree-lights.lock      ← Lock-fil for denne widgeten
├── dev-github.blade.php
├── .dev-github.lock               ← Lock-fil for denne widgeten
└── ...
```

**Fordel**: 
- Kun widgets uten `.lock` fil kan endres
- 100% isolasjon mellom widgets
- Synlig i filsystemet

### Workflow

```bash
# 1. Sjekk hvilke widgets som er låst
./scripts/widget-status.sh

# 2. Lås opp DEN SPESIFIKKE widgeten du skal jobbe med
./scripts/unlock-widget.sh season-tree-lights

# 3. Gjør endringer (KUN i denne widgeten!)
nano resources/views/widgets/season-tree-lights.blade.php

# 4. Lås igjen når ferdig
./scripts/lock-widget.sh season-tree-lights "Added Spotify integration"
```

### Git Pre-commit Hook

Når du prøver å committe:

```bash
git add resources/views/widgets/season-tree-lights.blade.php
git commit -m "Update tree"
```

Hook kjører og sjekker:
1. ✅ Kjører PHP widget-integrity check
2. ✅ Sjekker om .lock-fil eksisterer for endrede widgets
3. ❌ BLOKKERER hvis widget er låst
4. ✅ TILLATER hvis widget er ulåst

## 🚀 QUICK START

### Redigere en widget:

```bash
./scripts/unlock-widget.sh <widget-name>
# ... gjør endringer ...
./scripts/lock-widget.sh <widget-name> "What you did"
```

### Se status:

```bash
./scripts/widget-status.sh
```

### Lås alt (initial setup allerede gjort):

```bash
./scripts/lock-all-widgets.sh "Reason"
```

## 📊 CURRENT STATUS

**Kjørt 2025-10-11 10:46:45:**

```
✅ Newly locked: 40 widgets
   Total protected: 40 widgets
   Protection: 100%
```

**Alle widgets:**
- analytics-smartesider
- analytics-traffic
- business-folio
- business-stripe
- communication-phonero
- communication-sms
- crm-pipedrive
- crm-support
- demo-clock
- dev-github
- mail-failed-jobs
- mail-imap
- mail-log
- mail-queue
- mail-smtp
- monitoring-uptime
- news-rss
- project-trello
- season-fireplace
- season-sleigh-tracker
- season-snow-globe
- season-tree-lights
- security-events
- security-ssl-certs
- system-cpu-cores
- system-cpu-ram
- system-cron-jobs
- system-disk
- system-disk-io
- system-disk-usage
- system-error-log
- system-loadgraph
- system-megabox
- system-mood
- system-network
- system-uptime
- tools-bills
- tools-quicklinks
- weather-power-price
- weather-yr

**Alle er nå låst med individuelle `.lock` filer!**

## 🤖 FOR AI (GitHub Copilot, Claude, etc.)

**KRITISK REGEL:**

```bash
# ALLTID før du foreslår widget-endringer:
./scripts/widget-status.sh | grep <widget-name>

# Hvis låst (🔒):
./scripts/unlock-widget.sh <widget-name>

# Gjør endringer (KUN i denne widgeten)

# Lås igjen:
./scripts/lock-widget.sh <widget-name> "What was changed"
```

**ALDRI**:
- ❌ Endre flere widgets samtidig
- ❌ Endre en widget uten å sjekke lock-status først
- ❌ Slett .lock filer manuelt

**ALLTID**:
- ✅ Unlock → Edit → Lock workflow
- ✅ Inkluder grunn når du låser
- ✅ Verifiser integritet før locking

## 📚 DOKUMENTASJON

- **Full guide**: `AI-learned/PER-WIDGET-LOCK-SYSTEM.md`
- **Safety handbook**: `AI-learned/AI-SAFETY-GUARDRAILS.md`
- **Quick ref**: `scripts/WIDGET-LOCKS-README.md`

## ✅ FERDIG!

Systemet er installert og klart. 

**Eneste gjenværende steg**:
- [ ] Installer pre-commit hook (krever root/sudo)

**Test systemet**:
```bash
# Test unlock
./scripts/unlock-widget.sh demo-clock

# Test status
./scripts/widget-status.sh

# Test lock
./scripts/lock-widget.sh demo-clock "Test complete"
```

---

**Opprettet**: 2025-10-11 10:46  
**System**: Per-Widget Lock Protection v2.0  
**Status**: ✅ PRODUCTION READY
