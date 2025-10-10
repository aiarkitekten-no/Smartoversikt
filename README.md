# 📊 Smartoversikt# Laravel Widget Dashboard 🎯# Smartesider Live Dashboard# Smartesider Live Dashboard# Smartesider Live Dashboard# Smartesider Live Dashboard# Smartesider Live Dashboard<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>



> Smart business dashboard with 30+ customizable widgets for monitoring your entire business infrastructure



[![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=flat&logo=laravel)](https://laravel.com)Et kraftig, modulært widget-system bygget med Laravel 11, Alpine.js og Tailwind CSS.

[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=flat&logo=alpine.js)](https://alpinejs.dev)

[![Vite](https://img.shields.io/badge/Vite-6.3-646CFF?style=flat&logo=vite)](https://vitejs.dev)

[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.x-38B2AC?style=flat&logo=tailwind-css)](https://tailwindcss.com)

## ✨ FunksjonerEt komplett, live dashboard uten mock-data eller placeholders. Bygget med Laravel 11, Blade og Alpine.js.

## ✨ Features



### 🎯 30+ Widgets Across 8 Categories

### 🎯 Fase 0-3: Grunnmur

#### 🖥️ System Monitoring

- **System Uptime** - Server uptime, load average- ✅ Laravel 11 med Breeze autentisering

- **CPU & RAM** - Real-time usage monitoring

- **Disk Usage** - Storage space monitoring- ✅ Norsk brukergrensesnitt## 📋 ProsjektstatusEt komplett, live dashboard uten mock-data eller placeholders. Bygget med Laravel 11, Blade og Alpine.js.

- **Disk I/O** - Read/write performance

- **Network** - Traffic statistics- ✅ Modulært widget-system med fetchers

- **Cron Jobs** - Scheduled tasks overview

- **Error Log** - Latest system errors- ✅ System-monitoring widgets (Oppetid, CPU/RAM, Disk)



#### 📧 Mail Management- ✅ Automatisk data-refresh med konfigurerbare intervaller

- **IMAP** - Inbox monitoring with unread counts

- **SMTP** - Outgoing mail status- ✅ Snapshot-basert caching### ✅ Fase 0 - Guardrails & Struktur (FULLFØRT)

- **Mail Queue** - Pending email jobs

- **Failed Jobs** - Failed email tracking

- **Mail Log** - Recent email activity

### 📊 Fase 4: Widget-administrasjon

#### 💼 Business Intelligence

- **Folio Accounting** - Real-time balance & transactions (GraphQL)- ✅ Admin-panel for widget-håndtering

- **Stripe Payments** - Revenue, customers, MRR tracking

- **Smartesider Analytics** - Traffic & revenue statistics- ✅ Kategorisering av widgets**Mål**: Legg grunnmuren og bevis at vi jobber uten juks, mock eller placebo.## 📋 ProsjektstatusEt komplett, live dashboard uten mock-data eller placeholders. Bygget med Laravel 11, Blade og Alpine.js.



#### 📱 Communication- ✅ Bulk-operasjoner (aktiver/deaktiver/refresh)

- **Send SMS** - Interactive SMS sending with SMStools API

- **Phonero Telephony** - Click-to-dial & call history- ✅ Filtrering og søk

  - Queue status monitoring

  - Recent call log (answered/missed)- ✅ Rekkefølge-administrasjon

  - One-click dialing

#### Implementert:

#### 📊 Project Management

- **Trello Tasks** - Card counts by list (Planlagt, Pågår, Ferdig, Bugs, Ønsker)### 👤 Fase 5: Bruker-tilpassede dashboards

- **GitHub Activity** - Commits, repos, events tracking

- ✅ Personlige widgets per bruker- ✅ Laravel 11 installert (PHP 8.3, Composer 2.8)

#### 🔒 Security

- **SSL Certificates** - Expiry monitoring- ✅ Legg til/fjern widgets

- **Firewall Events** - Security event log with IP blocking

- **Failed Login Attempts** - Brute force detection- ✅ Widget-picker modal- ✅ AI-learned/ struktur opprettet med alle JSON-filer### ✅ Fase 0 - Guardrails & Struktur (FULLFØRT)



#### 🌤️ Weather & Energy- ✅ Individuelle innstillinger per widget

- **Yr.no Weather** - Norwegian weather forecasts

- **Power Prices** - Nordpool electricity pricing- ✅ Vis/skjul funksjonalitet- ✅ Banned-ord sjekk implementert (`scripts/banned-words-check.sh`)



#### 📰 News & CRM

- **RSS Feeds** - Customizable news sources

- **Pipedrive CRM** - Deals & pipeline overview### 🎨 Fase 6: Avansert & Polish- ✅ Read-only wrapper for OS-kommandoer (`app/Support/Sys/ReadonlyCommand.php`)

- **Support Tickets** - Customer support tracking

- ✅ **Drag-and-drop** - Dra widgets for å endre rekkefølge

## 🚀 Quick Start

- ✅ **Widget-innstillingsmodal** - Endre refresh interval og settings- ✅ Sikkerhet konfigurert (CSRF, HTTPS, rate-limit, logging)

### Prerequisites

- ✅ **Forbedret feilhåndtering** - Retry-logikk og bedre feilmeldinger

- PHP 8.1+

- Composer- ✅ **Live statusindikatorer** - Grønn/gul/rød status-dot- ✅ Unit-tester (4/4 passed)**Mål**: Legg grunnmuren og bevis at vi jobber uten juks, mock eller placebo.## 📋 ProsjektstatusEt komplett, live dashboard uten mock-data eller placeholders. Bygget med Laravel 11, Blade og Alpine.js.

- Node.js 18+

- MariaDB 10.6+ (MySQL kompatibel)- ✅ **Loading states** - Spinner og visuell feedback



### Installation- ✅ **Responsivt design** - Fungerer på mobil, tablet og desktop



```bash

# Clone repository

git clone https://github.com/aiarkitekten-no/Smartoversikt.git## 🚀 Bruk**Rapport**: [AI-learned/FASE-0-RAPPORT.md](AI-learned/FASE-0-RAPPORT.md)

cd Smartoversikt



# Install dependencies

composer install### Dashboard

npm install

1. Logg inn på https://nytt.smartesider.no

# Configure environment

cp .env.example .env2. Se dine personlige widgets---#### Implementert:

php artisan key:generate

3. Klikk "⚙️" for å endre innstillinger

# Setup database (MariaDB required)
# Create database first, then configure .env with credentials

php artisan migrate

php artisan db:seed --class=AdminUserSeeder5. Dra widgets (hold musepeker over "⋮⋮") for å endre rekkefølge

php artisan db:seed --class=WidgetCatalogSeeder

6. Klikk "+ Legg til widget" for å legge til flere### ✅ Fase 1 - Innlogging (FULLFØRT)- ✅ Laravel 11 installert (PHP 8.3, Composer 2.8)

# Build assets

npm run build



# Start development server### Admin

php artisan serve

```1. Gå til /admin/widgets



### First Login2. Se alle tilgjengelige widgets**Mål**: Implementer autentisering med Laravel Breeze og tilpass UX til norsk.- ✅ AI-learned/ struktur opprettet med alle JSON-filer### ✅ Fase 0 - Guardrails & Struktur (FULLFØRT)



Default credentials:3. Filtrer på kategori eller status

- Email: `admin@example.com`

- Password: `password`4. Aktiver/deaktiver widgets



**⚠️ Change these immediately in production!**5. Kjør bulk-operasjoner



## 🔧 Configuration#### Implementert:- ✅ Banned-ord sjekk implementert (`scripts/banned-words-check.sh`)



### Widget API Keys## 📦 Tilgjengelige Widgets



Configure API integrations in `.env`:- ✅ Laravel Breeze installert (Blade stack)



```env| Widget | Beskrivelse | Kategori | Refresh |

# Folio Accounting

FOLIO_COOKIE=your_session_cookie|--------|-------------|----------|---------|- ✅ Login støtter både e-post OG brukernavn (automatisk deteksjon)- ✅ Read-only wrapper for OS-kommandoer (`app/Support/Sys/ReadonlyCommand.php`)- ✅ Laravel 11 installert (PHP 8.3)

FOLIO_ORG=your_org_number

| system.uptime | Server oppetid og load average | System | 60s |

# Trello

TRELLO_API_KEY=your_api_key| system.cpu-ram | CPU og RAM bruk | System | 30s |- ✅ "Husk meg" i 30 dager (konfigurerbart)

TRELLO_API_TOKEN=your_token

TRELLO_BOARD_ID=your_board_id| system.disk | Diskplass og I/O | System | 120s |



# GitHub| demo.clock | Live klokke | Demo | 10s |- ✅ AdminUserSeeder leser passord fra .env (ingen hardkodet data)- ✅ Sikkerhet konfigurert (CSRF, HTTPS, rate-limit, logging)

GITHUB_TOKEN=your_personal_access_token

GITHUB_USERNAME=your_username



# SMStools## 🛠️ Teknisk Stack- ✅ Norsk UX med "Vis passord"-toggle (Alpine.js)

SMSTOOLS_CLIENT_ID=your_client_id

SMSTOOLS_CLIENT_SECRET=your_client_secret

SMS_SENDER=YourName

- **Backend:** Laravel 11.46.1, PHP 8.3.26- ✅ Dashboard-banner viser login-status og expiry- ✅ Unit-tester (4/4 passed)- ✅ AI-learned/ struktur## 📋 ProsjektstatusEt komplett, live dashboard uten mock-data eller placeholders. Bygget med Laravel 11, Blade og Alpine.js.<p align="center">

# Phonero Telephony

PHONERO_API_URL=https://bedriftsnett-api.phonero.net- **Database:** MariaDB 10.6+

PHONERO_USERNAME=your_username

PHONERO_PASSWORD=your_password- **Frontend:** Alpine.js 3.x, Tailwind CSS 3.x

PHONERO_CUSTOMER_ID=your_customer_id

PHONERO_DEFAULT_AGENT=agent_number- **Build:** Vite 6.3.6

PHONERO_DEFAULT_CLI=caller_id_number

- **Server:** Plesk / Apache#### Manuelt steg:

# Stripe

STRIPE_SECRET=your_stripe_secret_key



# Weather## 📝 Lage ny widget⏳ **VENTER**: Sett `ADMIN_PASSWORD` i `.env`, deretter kjør:

YR_LOCATION_NAME=Oslo

YR_LATITUDE=59.9139

YR_LONGITUDE=10.7522

```Se [DEVELOPMENT.md](DEVELOPMENT.md) for detaljert guide.```bash**Rapport**: [AI-learned/FASE-0-RAPPORT.md](AI-learned/FASE-0-RAPPORT.md)- ✅ Banned-ord sjekk



### Widget Refresh



Widgets auto-refresh based on their configured intervals. Manual refresh:## 🔧 Artisan Kommandoerphp artisan db:seed --class=AdminUserSeeder



```bash

# Refresh all widgets

php artisan widgets:refresh```bash```



# Refresh specific widget# Refresh alle widgets

php artisan widgets:refresh system.cpu-ram

php artisan widgets:refresh

# Force refresh (ignore cache)

php artisan widgets:refresh --force

```

# Refresh spesifikk widget**Rapport**: Integrert i dette dokumentet---- ✅ ReadonlyCommand wrapper

### Scheduled Tasks

php artisan widgets:refresh --widget=system.uptime

Add to crontab for automatic widget updates:



```cron

* * * * * cd /path/to/smartoversikt && php artisan schedule:run >> /dev/null 2>&1# Force refresh (ignorer cache)

```

php artisan widgets:refresh --force---

## 🎨 Customization

```

### Adding Widgets to Dashboard



1. Navigate to **Settings** → **Widget Library**

2. Click **+ Add Widget** on desired widget## 🐛 Troubleshooting

3. Drag & drop to reorder

4. Adjust size (1-4 columns wide)### ✅ Fase 2 - Widget-rammeverk (FULLFØRT)### ✅ Fase 1 - Innlogging (FULLFØRT)- ✅ Sikkerhet (CSRF, HTTPS, rate-limit)



### Creating Custom Widgets**Widget viser "HTTP 500":**



1. Create fetcher class:- Sjekk at fetcher-klassen eksisterer



```php- Se `storage/logs/laravel.log` for detaljer

// app/Services/Widgets/CustomWidgetFetcher.php

class CustomWidgetFetcher extends BaseWidgetFetcher- Kjør `php artisan config:clear`**Mål**: Implementer widget-arkitektur fra database til frontend.

{

    protected string $widgetKey = 'custom.widget';

    protected int $refreshIntervalSeconds = 300;

    **Drag-and-drop fungerer ikke:**

    protected function fetchData(): array

    {- Sørg for at du holder musepeker over "⋮⋮" ikonet

        return [

            'timestamp' => now()->toIso8601String(),- Widgets må ha `draggable="true"` attributt#### Implementert:**Mål**: Implementer autentisering med Laravel Breeze og tilpass UX til norsk.### ✅ Fase 0 - Guardrails & Struktur (FULLFØRT)<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>

            'data' => 'your data here',

        ];

    }

}**Data oppdateres ikke:**- ✅ Database-skjema (widgets, user_widgets, widget_snapshots)

```

- Sjekk refresh interval i widget-innstillinger

2. Create Blade view:

- Verifiser at widget er aktiv- ✅ Eloquent-modeller med relasjoner

```blade

<!-- resources/views/widgets/custom-widget.blade.php -->- Se browser console for JavaScript-feil

<div class="widget-card" x-data="widgetData('custom.widget')" x-init="init()">

    <h3 class="text-lg font-semibold">Custom Widget</h3>- ✅ Widget-katalog i `config/widgets.php`

    <div x-text="data.data"></div>

</div>## 📄 Lisens

```

- ✅ WidgetCatalogSeeder (4 widgets definert)#### Implementert:### ✅ Fase 1 - Innlogging (FULLFØRT)

3. Register in `config/widgets.php`:

MIT License

```php

'custom.widget' => [- ✅ BaseWidgetFetcher (abstract class med snapshot-caching)

    'name' => 'Custom Widget',

    'description' => 'Your custom widget',---

    'category' => 'custom',

    'refresh_interval' => 300,- ✅ DemoClockFetcher (fungerende demo-widget)- ✅ Laravel Breeze installert (Blade stack)

    'fetcher' => \App\Services\Widgets\CustomWidgetFetcher::class,

],**Status:** ✅ Fase 0-6 fullført  

```

**Versjon:** 1.0.0  - ✅ Widget API-controller med Sanctum-auth

4. Seed database:

**Utviklet av:** Terje @ Smartesider.no

```bash

php artisan db:seed --class=WidgetCatalogSeeder- ✅ API-routes (`/api/widgets`, `/api/widgets/{key}`, refresh)- ✅ Login støtter både e-post OG brukernavn (automatisk deteksjon)- ✅ Laravel Breeze (Blade)

```

- ✅ Alpine.js frontend-komponent (`widgetData()`)

## 📱 Interactive Widgets

- ✅ Blade-partial for demo-clock widget- ✅ "Husk meg" i 30 dager (konfigurerbart)

### SMS Widget

- ✅ RefreshWidgetsCommand (artisan + scheduler)

Send SMS directly from dashboard:

- Enter phone number (Norwegian format: 8 digits or +47 prefix)- ✅ Scheduled tasks konfigurert (kjører hvert minutt)- ✅ AdminUserSeeder leser passord fra .env (ingen hardkodet data)- ✅ Login: e-post ELLER brukernavn

- Type message (max 160 chars)

- Click **Send**

- See sent counter in footer

**Rapport**: [AI-learned/FASE-2-RAPPORT.md](AI-learned/FASE-2-RAPPORT.md)- ✅ Norsk UX med "Vis passord"-toggle (Alpine.js)

### Phonero Telephony Widget



Click-to-dial functionality:

- Enter phone number---- ✅ Dashboard-banner viser login-status og expiry- ✅ "Husk meg" i 30 dager**Mål**: Legg grunnmuren og bevis at vi jobber uten juks, mock eller placebo.## 📋 Prosjektstatus<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>

- Click **📞 RING** to initiate call

- View call status (Ringer/Samtale pågår)

- See recent call history (✅ answered, ❌ missed)

- Monitor queue status & wait times### ✅ Fase 3 - System Widgets (FULLFØRT)



## 🏗️ Architecture



### Tech Stack**Mål**: Implementer system-monitoring widgets med ReadonlyCommand.#### Manuelt steg:- ✅ AdminUserSeeder (fra .env)



- **Backend**: Laravel 10.x

- **Frontend**: Alpine.js 3.x + Blade templates

- **Build Tool**: Vite 6.3.6#### Implementert:⏳ **VENTER**: Sett `ADMIN_PASSWORD` i `.env`, deretter kjør:

- **CSS Framework**: Tailwind CSS 3.x

- **Database**: MariaDB 10.6+ (MySQL kompatibel)- ✅ **SystemUptimeFetcher** – Server uptime og load average

- **HTTP Client**: Guzzle for API integrations

  - Leser `/proc/uptime` og `/proc/loadavg````bash- ✅ Norsk UX + "Vis passord"

### Widget System

  - Viser boot time og uptime formatert (15 dager, 55 minutter)

1. **Fetcher** - Retrieves data from external APIs or system

2. **Snapshot** - Caches data with configurable TTL  - Load average 1m/5m/15mphp artisan db:seed --class=AdminUserSeeder

3. **API Endpoint** - Serves data to frontend via JSON

4. **Blade View** - Renders widget UI  - Refresh: 60 sekunder

5. **Alpine.js** - Reactive data binding & interactions

  ```- ⏳ **VENTER**: ADMIN_PASSWORD i .env

### Data Flow

- ✅ **SystemCpuRamFetcher** – Memory og CPU monitoring

```

External API → Fetcher → WidgetSnapshot (DB) → API Controller → Blade View → Alpine.js  - Leser `/proc/meminfo` og `/proc/loadavg`

```

  - Memory usage: 14.95 GB / 125.65 GB (11.9%)

Widgets refresh automatically based on TTL. Frontend polls `/api/widgets/{key}` endpoint.

  - Swap usage tracking**Rapport**: Integrert i dette dokumentet#### Implementert:<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>

## 🔐 Security Features

  - Running/total processes

- **IP Blocking** - Automated firewall integration

- **Failed Login Tracking** - Brute force detection  - Visuell progress bar med fargekoding

- **SSL Monitoring** - Certificate expiry alerts

- **CSRF Protection** - Laravel's built-in protection  - Refresh: 30 sekunder

- **XSS Prevention** - Blade template escaping

- **Secret Scanning** - GitHub push protection enabled  ---### 🔄 Fase 2 - Widget-rammeverk (NESTE)



## 📊 API Integrations- ✅ **SystemDiskFetcher** – Diskplass monitoring



### Supported Services  - Kjører `df -B1` og `df -i` via ReadonlyCommand



| Service | Purpose | Auth Method |  - Filesystem usage per mount point

|---------|---------|-------------|

| Folio | Accounting data | Cookie session |  - Inode usage (avansert, ekspanderbar)### ✅ Fase 2 - Widget-rammeverk (FULLFØRT)- DB-skjema for widgets- ✅ Laravel 11 installert (PHP 8.3, Composer 2.8)

| Trello | Project management | API key + token |

| GitHub | Developer activity | Personal access token |  - Filtrerer pseudo-filesystems

| SMStools | SMS sending | Client ID/Secret |

| Phonero | Telephony | Username/Password → SessionID |  - Refresh: 120 sekunder

| Stripe | Payment tracking | Secret key |

| Yr.no | Weather forecasts | Public API |

| Nordpool | Power prices | Public API |

- ✅ Dashboard oppdatert med alle 4 widgets (3 system + 1 demo)**Mål**: Implementer widget-arkitektur fra database til frontend.- Komponentmønster

### Rate Limiting

- ✅ Responsive grid layout (1/2/3 kolonner)

Widget refresh intervals prevent API rate limit issues:

- System widgets: 10-30 seconds- ✅ Frontend Blade components med Alpine.js

- External APIs: 60-300 seconds

- Manual refresh: Always allowed- ✅ Auto-refresh per widget type



## 🐛 Troubleshooting#### Implementert:- Scheduler- ✅ AI-learned/ struktur opprettet med alle JSON-filer### ✅ Fase 0 - Guardrails & Struktur (FULLFØRT)<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>



### Widget Not Updating#### Test:



```bash```bash- ✅ Database-skjema (widgets, user_widgets, widget_snapshots)

# Check snapshot status

php artisan widgets:refresh widget.key --forcephp artisan widgets:refresh --force



# View logs# Summary: 4 refreshed, 0 skipped, 0 failed- ✅ Eloquent-modeller med relasjoner

tail -f storage/logs/laravel.log

``````



### Authentication Errors- ✅ Widget-katalog i `config/widgets.php`



```bash**Rapport**: [AI-learned/FASE-3-RAPPORT.md](AI-learned/FASE-3-RAPPORT.md)

# Clear config cache

php artisan config:clear- ✅ WidgetCatalogSeeder (4 widgets definert)## 🚀 Rask Start- ✅ Banned-ord sjekk implementert (`scripts/banned-words-check.sh`)



# Verify .env credentials---

cat .env | grep API_KEY

```- ✅ BaseWidgetFetcher (abstract class med snapshot-caching)



### Frontend Not Compiling### 🔄 Fase 4+ - Kommende faser



```bash- ✅ DemoClockFetcher (fungerende demo-widget)

# Clear cache

npm run build- **Fase 4**: Mail & Queue widgets (postqueue, failed jobs, mail log)

php artisan view:clear

- **Fase 5**: Eksterne API widgets (Yr.no vær, Smartesider.no stats)- ✅ Widget API-controller med Sanctum-auth

# Check Vite manifest

ls -la public/build/- **Fase 6**: Dashboard-layout customization (drag-and-drop)

```

- **Fase 7**: Admin-panel for widget-konfigurasjon- ✅ API-routes (`/api/widgets`, `/api/widgets/{key}`, refresh)### 1. Sett opp .env- ✅ Read-only wrapper for OS-kommandoer (`app/Support/Sys/ReadonlyCommand.php`)</p>

## 🤝 Contributing

- **Fase 8**: Produksjons-deployment og optimering

Contributions welcome! Please:

- ✅ Alpine.js frontend-komponent (`widgetData()`)

1. Fork the repository

2. Create feature branch (`git checkout -b feature/amazing-widget`)---

3. Commit changes (`git commit -m 'Add amazing widget'`)

4. Push to branch (`git push origin feature/amazing-widget`)- ✅ Blade-partial for demo-clock widget```bash

5. Open Pull Request

## 🚀 Rask Start

## 📄 License

- ✅ RefreshWidgetsCommand (artisan + scheduler)

This project is open-sourced software licensed under the [MIT license](LICENSE).

### 1. Sett opp .env

## 🙏 Acknowledgments

```bash- ✅ Scheduled tasks konfigurert (kjører hvert minutt)# Kopier og rediger- ✅ Dashboard config opprettet (`config/dashboard.php`)

- Laravel Framework

- Alpine.jscp .env.example .env

- Tailwind CSS

- All API providers (Folio, Trello, GitHub, etc.)php artisan key:generate



---



**Built with ❤️ by [AI Arkitekten](https://aiarkitekten.no)**# Legg til admin-passord (for Fase 1)#### Test widget-refresh:cp .env.example .env



**Repository**: [github.com/aiarkitekten-no/Smartoversikt](https://github.com/aiarkitekten-no/Smartoversikt)echo "ADMIN_PASSWORD=DittSikre\$Passord123" >> .env


``````bash



### 2. Installer avhengigheter# Refresh én widget- ✅ Sikkerhetskonfigurasjon: CSRF aktiv, HTTPS tvunget, rate-limiting**Mål**: Legg grunnmuren og bevis at vi jobber uten juks, mock eller placebo.

```bash

composer installphp artisan widgets:refresh demo.clock --force

npm install && npm run build

```# VIKTIG: Sett ADMIN_PASSWORD



### 3. Kjør migrations og seeders# Refresh alle widgets

```bash

php artisan migratephp artisan widgets:refresh --forcenano .env- ✅ Logging: daily driver, 30 dagers retensjon

php artisan db:seed --class=WidgetCatalogSeeder

php artisan db:seed --class=AdminUserSeeder

```

# Vis scheduled tasks# Finn: ADMIN_PASSWORD=

### 4. Test widget-system

```bashphp artisan schedule:list

# Refresh alle widgets

php artisan widgets:refresh --force```# Sett til: ADMIN_PASSWORD=ditt_sikre_passord- ✅ Plesk docroot konfigurert: `/var/www/vhosts/smartesider.no/nytt.smartesider.no/public`## About Laravel



# Sjekk snapshots i database

php artisan tinker --execute="dd(App\Models\WidgetSnapshot::all()->pluck('widget_key'));"

```**Rapport**: [AI-learned/FASE-2-RAPPORT.md](AI-learned/FASE-2-RAPPORT.md)```



### 5. Start utvikling

```bash

# Start Laravel-server---

php artisan serve



# I egen terminal: Watch frontend assets

npm run dev### 🔄 Fase 3+ - Kommende faser### 2. Opprett admin bruker

```



### 6. Logg inn

Gå til `http://localhost:8000` og logg inn med:- **Fase 3**: System-widgets (uptime, cpu-ram, disk)```bash#### AI-learned filer:#### Implementert:

- **E-post**: `admin@smartesider.no`

- **Brukernavn**: `admin`- **Fase 4**: Dashboard-layout med drag-and-drop

- **Passord**: (det du satte i ADMIN_PASSWORD)

- **Fase 5**: Bruker-widget-innstillingerphp artisan db:seed --class=AdminUserSeeder

---

- **Fase 6**: Admin-panel for widget-administrasjon

## 📁 Prosjektstruktur

- **Fase 7**: Integrering med eksterne kilder (Yr.no, Smartesider.no API, etc.)```- `fungerer.json` - Ting vi har bevist fungerer

```

/var/www/vhosts/smartesider.no/nytt.smartesider.no/- **Fase 8**: Produksjons-deployment og optimering

├── AI-learned/                  # Tracking og rapporter

│   ├── fungerer.json            # Bevis på fungerende implementasjoner

│   ├── funksjoner.json          # Funksjons-register med hashes

│   ├── feil.json                # Feil vi har møtt og løst---

│   ├── usikkert.json            # Ting vi er usikre på

│   ├── godekilder.json          # Dokumentasjons-kilder### 3. Logg inn- `feil.json` - Metoder som er garantert feil- ✅ Laravel 11 installert (PHP 8.3, Composer 2.8)Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

│   ├── donetoday.json           # Kronologisk logg

│   ├── risiko.json              # Risikovurderinger## 🚀 Rask Start

│   ├── FASE-0-RAPPORT.md        # Fase 0 dokumentasjon

│   ├── FASE-2-RAPPORT.md        # Fase 2 dokumentasjon- Gå til: `https://nytt.smartesider.no/login`

│   └── FASE-3-RAPPORT.md        # Fase 3 dokumentasjon

│### 1. Sett opp .env

├── app/

│   ├── Console/Commands/```bash- Bruk: `terje@smartesider.no` (eller `Terje`) + ditt passord- `usikkert.json` - Hypoteser som trenger avklaring

│   │   └── RefreshWidgetsCommand.php  # Widget-refresh artisan command

│   ├── Http/Controllers/cp .env.example .env

│   │   ├── Api/

│   │   │   └── WidgetController.php   # Widget API endpointsphp artisan key:generate- Kryss av "Husk meg i 30 dager"

│   │   └── Auth/

│   │       └── LoginRequest.php       # Email/username login

│   ├── Models/

│   │   ├── Widget.php                 # Widget-modell# Legg til admin-passord (for Fase 1)- `godekilder.json` - Presise nett-kilder- ✅ AI-learned/ struktur opprettet med alle JSON-filer

│   │   ├── UserWidget.php             # Bruker-widget-tilordning

│   │   └── WidgetSnapshot.php         # Widget-snapshot cacheecho "ADMIN_PASSWORD=DittSikre$Passord123" >> .env

│   ├── Services/Widgets/

│   │   ├── BaseWidgetFetcher.php      # Abstract widget fetcher```## 🔒 Sikkerhet

│   │   ├── DemoClockFetcher.php       # Demo klokke-widget

│   │   ├── SystemUptimeFetcher.php    # Uptime & load widget

│   │   ├── SystemCpuRamFetcher.php    # CPU/RAM widget

│   │   └── SystemDiskFetcher.php      # Disk usage widget### 2. Installer avhengigheter- `funksjoner.json` - Register over funksjoner m/ hash og avhengigheter

│   └── Support/Sys/

│       └── ReadonlyCommand.php        # Sikker OS-kommando wrapper```bash

│

├── config/composer install- **Read-only wrapper**: Kun whitelisted OS-kommandoer

│   ├── dashboard.php            # Dashboard-konfigurasjon

│   └── widgets.php              # Widget-katalognpm install && npm run build

│

├── database/```- **Ingen hardkodet passord**: Alt i .env- `donetoday.json` - Kronologisk logg- ✅ Banned-ord sjekk implementert (`scripts/banned-words-check.sh`)- [Simple, fast routing engine](https://laravel.com/docs/routing).

│   ├── migrations/

│   │   └── 2025_10_06_104629_create_widgets_tables.php

│   └── seeders/

│       ├── AdminUserSeeder.php### 3. Kjør migrations og seeders- **CSRF + HTTPS**: Aktiv

│       └── WidgetCatalogSeeder.php

│```bash

├── resources/

│   ├── js/php artisan migrate- **Rate-limiting**: 60 req/min- `risiko.json` - Kjente risiki og mitigering

│   │   └── app.js               # Alpine.js komponenter

│   └── views/php artisan db:seed --class=WidgetCatalogSeeder

│       ├── auth/

│       │   └── login.blade.php  # Norsk login med passord-togglephp artisan db:seed --class=AdminUserSeeder- **Remember-token**: 30 dager

│       ├── widgets/

│       │   ├── demo-clock.blade.php         # Demo widget```

│       │   ├── system-uptime.blade.php      # Uptime widget

│       │   ├── system-cpu-ram.blade.php     # CPU/RAM widget- ✅ Read-only wrapper for OS-kommandoer (`app/Support/Sys/ReadonlyCommand.php`)- [Powerful dependency injection container](https://laravel.com/docs/container).

│       │   └── system-disk.blade.php        # Disk widget

│       └── dashboard.blade.php  # Dashboard med alle widgets### 4. Test widget-system

│

├── routes/```bash## 📁 Struktur

│   ├── api.php                  # API-routes (Sanctum auth)

│   ├── console.php              # Scheduled tasks# Refresh demo-widget

│   └── web.php                  # Web-routes

│php artisan widgets:refresh demo.clock --force### 🔄 Fase 1 - Innlogging (NESTE)

├── scripts/

│   └── banned-words-check.sh    # Valider ingen mock/placeholder-kode

│

└── tests/# Sjekk snapshot i database```

    └── Unit/

        └── ReadonlyCommandTest.php  # Unit-tester (4/4 passed)php artisan tinker --execute="dd(App\Models\WidgetSnapshot::latest()->first());"

```

```AI-learned/- Laravel Breeze (Blade)- ✅ Dashboard config opprettet (`config/dashboard.php`)- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.

---



## 🔐 Sikkerhet

### 5. Start utvikling  ├── FASE-0-RAPPORT.md  ← Fase 0 oppsummering

- ✅ CSRF-beskyttelse aktivert

- ✅ HTTPS enforced (session.secure = true)```bash

- ✅ Rate-limiting: 60 requests/minutt

- ✅ Password hashing med bcrypt# Start Laravel-server  ├── FASE-1-RAPPORT.md  ← Fase 1 oppsummering- "Husk meg" i 30 dager

- ✅ API-auth med Laravel Sanctum

- ✅ Read-only OS-kommandoer (whitelist-basert)php artisan serve

- ✅ Input-sanitering i ReadonlyCommand

- ✅ Logging av alle widget-refresh forsøk  ├── fungerer.json      ← 11 beviser



---# I egen terminal: Watch frontend assets



## 🧪 Testingnpm run dev  ├── funksjoner.json    ← 2 funksjoner- Admin bruker seeder- ✅ Sikkerhetskonfigurasjon: CSRF aktiv, HTTPS tvunget, rate-limiting- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).



### Kjør unit-tester```

```bash

php artisan test --filter ReadonlyCommandTest  └── donetoday.json     ← Full historikk

# PASS  Tests\Unit\ReadonlyCommandTest

# ✓ it can run whitelisted commands### 6. Logg inn

# ✓ it blocks non whitelisted commands

# ✓ it rejects blacklisted patternsGå til `http://localhost:8000` og logg inn med:

# ✓ it handles command timeout

# Tests:    4 passed (7 assertions)- **E-post**: `admin@smartesider.no`

```

- **Brukernavn**: `admin`app/Support/Sys/

### Sjekk banned-ord

```bash- **Passord**: (det du satte i ADMIN_PASSWORD)

bash scripts/banned-words-check.sh

# ✅ Ingen bannede ord funnet!  └── ReadonlyCommand.php  ← OS-wrapper (hash: a3f9c2e1b5d8)## 🔒 Sikkerhet- ✅ Logging: daily driver, 30 dagers retensjon- Database agnostic [schema migrations](https://laravel.com/docs/migrations).

```

---

### Test widget-refresh

```bash

php artisan widgets:refresh --force

# Refreshing: system.uptime## 📁 Prosjektstruktur

#   ✓ Success

# Refreshing: system.cpu-ramdatabase/seeders/

#   ✓ Success

# Refreshing: system.disk```

#   ✓ Success

# Refreshing: demo.clock/var/www/vhosts/smartesider.no/nytt.smartesider.no/  └── AdminUserSeeder.php  ← Admin fra .env (hash: 7f4e2a9c8b1d)

#   ✓ Success

# Summary: 4 refreshed, 0 skipped, 0 failed├── AI-learned/                  # Tracking og rapporter

```

│   ├── fungerer.json            # Bevis på fungerende implementasjoner### Guardrails- [Robust background job processing](https://laravel.com/docs/queues).

---

│   ├── funksjoner.json          # Funksjons-register med hashes

## 📚 Dokumentasjon

│   ├── feil.json                # Feil vi har møtt og løstresources/views/

- **AI-learned/FASE-0-RAPPORT.md**: Guardrails, struktur, sikkerhet

- **AI-learned/FASE-2-RAPPORT.md**: Widget-framework end-to-end│   ├── usikkert.json            # Ting vi er usikre på

- **AI-learned/FASE-3-RAPPORT.md**: System widgets med ReadonlyCommand

- **config/dashboard.php**: Dashboard-konfigurasjon (remember_days, timezone)│   ├── godekilder.json          # Dokumentasjons-kilder  ├── auth/login.blade.php      ← Norsk + vis passord- **Read-only wrapper**: Kun whitelisted kommandoer

- **config/widgets.php**: Widget-katalog (alle tilgjengelige widgets)

- **AI-learned/fungerer.json**: Bevis på alle fungerende implementasjoner│   ├── donetoday.json           # Kronologisk logg

- **AI-learned/funksjoner.json**: Funksjons-register med SHA-256 hashes

│   ├── risiko.json              # Risikovurderinger  └── dashboard.blade.php        ← Banner med utløpstid

---

│   ├── FASE-0-RAPPORT.md        # Fase 0 dokumentasjon

## 🛠 Teknologier

│   └── FASE-2-RAPPORT.md        # Fase 2 dokumentasjon```- **Banned-ord sjekk**: Automatisk sjekk før hver fase#### AI-learned filer:- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

- **Backend**: Laravel 11.46.1 (PHP 8.3.26)

- **Frontend**: Blade templates + Alpine.js 3.x│

- **Database**: MariaDB 10.6+ (MySQL kompatibel)

- **CSS**: Tailwind CSS (via Breeze)├── app/

- **Build**: Vite 6.3.6

- **API Auth**: Laravel Sanctum 4.2.0│   ├── Console/Commands/

- **Testing**: PHPUnit (Laravel default)

- **Hosting**: Plesk med PHP-FPM 8.3│   │   └── RefreshWidgetsCommand.php  # Widget-refresh artisan command## 🧪 Testing- **Hemmeligheter**: Kun i `.env`, aldri sjekket inn



---│   ├── Http/Controllers/



## 📞 Kontakt│   │   ├── Api/



**Prosjekt**: Smartesider Live Dashboard  │   │   │   └── WidgetController.php   # Widget API endpoints

**Eier**: Smartesider.no  

**Opprettet**: 6. oktober 2025  │   │   └── Auth/```bash- **CSRF**: Aktivert globalt- `fungerer.json` - Ting vi har bevist fungerer

**Status**: Fase 3 fullført, Fase 4+ under planlegging  

│   │       └── LoginRequest.php       # Email/username login

**Live widgets:**

- 🖥️ System Uptime & Load│   ├── Models/# Banned-ord sjekk

- 💾 CPU & RAM monitoring

- 💿 Disk usage tracking│   │   ├── Widget.php                 # Widget-modell

- 🕐 Demo clock (test widget)

│   │   ├── UserWidget.php             # Bruker-widget-tilordning./scripts/banned-words-check.sh- **HTTPS**: Tvunget i produksjon

**Strenge regler**:

- ❌ Ingen mock-data│   │   └── WidgetSnapshot.php         # Widget-snapshot cache

- ❌ Ingen placeholders

- ❌ Ingen "kommer snart"-meldinger│   ├── Services/Widgets/

- ✅ Kun fungerende, testet kode

- ✅ AI-learned tracking av alt│   │   ├── BaseWidgetFetcher.php      # Abstract widget fetcher



---│   │   └── DemoClockFetcher.php       # Demo klokke-widget# Enhetstester- **Rate-limiting**: 60 req/min per bruker- `feil.json` - Metoder som er garantert feilLaravel is accessible, powerful, and provides tools required for large, robust applications.



## 📝 Lisens│   └── Support/Sys/



Dette er et proprietært prosjekt for Smartesider.no.│       └── ReadonlyCommand.php        # Sikker OS-kommando wrapperphp artisan test


│

├── config/

│   ├── dashboard.php            # Dashboard-konfigurasjon

│   └── widgets.php              # Widget-katalog# Sjekk seeder

│

├── database/php artisan db:seed --class=AdminUserSeeder### Funksjons-hashing- `usikkert.json` - Hypoteser som trenger avklaring

│   ├── migrations/

│   │   └── 2025_10_06_104629_create_widgets_tables.php```

│   └── seeders/

│       ├── AdminUserSeeder.phpAlle funksjoner merkes med:

│       └── WidgetCatalogSeeder.php

│## 📝 Kjøreregler

├── resources/

│   ├── js/```php- `godekilder.json` - Presise nett-kilder## Learning Laravel

│   │   └── app.js               # Alpine.js komponenter

│   └── views/1. Les `AI-learned/*` før hver fase

│       ├── auth/

│       │   └── login.blade.php  # Norsk login med passord-toggle2. Kjør `./scripts/banned-words-check.sh`# START {hash} / Beskrivelse

│       ├── widgets/

│       │   └── demo-clock.blade.php  # Demo widget partial3. Logg i `donetoday.json`

│       └── dashboard.blade.php  # Dashboard med status-banner

│4. Oppdater `fungerer.json` / `feil.json`// kode her- `funksjoner.json` - Register over funksjoner m/ hash og avhengigheter

├── routes/

│   ├── api.php                  # API-routes (Sanctum auth)5. **STOPP** hvis noe er uklart → `usikkert.json`

│   ├── console.php              # Scheduled tasks

│   └── web.php                  # Web-routes# SLUTT {hash}

│

├── scripts/## 🔗 Dokumentasjon

│   └── banned-words-check.sh    # Valider ingen mock/placeholder-kode

│```- `donetoday.json` - Kronologisk loggLaravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

└── tests/

    └── Unit/- [FASE-0-RAPPORT.md](AI-learned/FASE-0-RAPPORT.md) - Guardrails & struktur

        └── ReadonlyCommandTest.php  # Unit-tester (4/4 passed)

```- [FASE-1-RAPPORT.md](AI-learned/FASE-1-RAPPORT.md) - Innlogging



---- [Laravel 11 Docs](https://laravel.com/docs/11.x)



## 🔐 Sikkerhet- [Laravel Breeze](https://laravel.com/docs/11.x/starter-kits#laravel-breeze)Hash registreres i `AI-learned/funksjoner.json` med avhengigheter og følgefeil.- `risiko.json` - Kjente risiki og mitigering



- ✅ CSRF-beskyttelse aktivert

- ✅ HTTPS enforced (session.secure = true)

- ✅ Rate-limiting: 60 requests/minutt## 👤 Kontakt

- ✅ Password hashing med bcrypt

- ✅ API-auth med Laravel Sanctum

- ✅ Read-only OS-kommandoer (whitelist-basert)

- ✅ Input-sanitering i ReadonlyCommandTerje - terje@smartesider.no## 🛠 Teknisk StackYou may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

- ✅ Logging av alle widget-refresh forsøk



---

---

## 🧪 Testing



### Kjør unit-tester

```bash**Status**: ✅ Fase 1 fullført | ⏳ Venter på ADMIN_PASSWORD | 🚀 Klar for Fase 2- **Backend**: Laravel 11 (PHP 8.3)### 🔄 Fase 1 - Innlogging (NESTE)

php artisan test --filter ReadonlyCommandTest

# PASS  Tests\Unit\ReadonlyCommandTest

# ✓ it can run whitelisted commands- **Frontend**: Blade templates + Alpine.js

# ✓ it blocks non whitelisted commands

# ✓ it rejects blacklisted patterns- **Database**: MariaDB 10.6+ (MySQL kompatibel)- Laravel Breeze (Blade)If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

# ✓ it handles command timeout

# Tests:    4 passed (7 assertions)- **Cache**: Database driver

```

- **Jobs**: Laravel Scheduler (cron)- "Husk meg" i 30 dager

### Sjekk banned-ord

```bash- **Hosting**: Plesk (PHP-FPM 8.3)

bash scripts/banned-words-check.sh

# ✅ Ingen bannede ord funnet!- **Docroot**: `/var/www/vhosts/smartesider.no/nytt.smartesider.no/public`- Admin bruker seeder## Laravel Sponsors

```



### Test widget-refresh

```bash## 📁 Prosjektstruktur

php artisan widgets:refresh demo.clock --force

# Refreshing widget: demo.clock

# ✓ demo.clock refreshed successfully.

``````## 🔒 SikkerhetWe would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).



---public/                             - Plesk docroot (tilgjengelig via web)



## 📚 Dokumentasjonapp/



- **AI-learned/FASE-0-RAPPORT.md**: Guardrails, struktur, sikkerhet  Support/Sys/ReadonlyCommand.php  - Sikker wrapper for OS-kommandoer

- **AI-learned/FASE-2-RAPPORT.md**: Widget-framework end-to-end

- **config/dashboard.php**: Dashboard-konfigurasjon (remember_days, timezone)config/### Guardrails### Premium Partners

- **config/widgets.php**: Widget-katalog (alle tilgjengelige widgets)

- **AI-learned/fungerer.json**: Bevis på alle fungerende implementasjoner  dashboard.php                     - Dashboard-spesifikk konfig

- **AI-learned/funksjoner.json**: Funksjons-register med SHA-256 hashes

AI-learned/                         - Læring og dokumentasjon- **Read-only wrapper**: Kun whitelisted kommandoer

---

  fungerer.json

## 🛠 Teknologier

  feil.json- **Banned-ord sjekk**: Automatisk sjekk før hver fase- **[Vehikl](https://vehikl.com/)**

- **Backend**: Laravel 11.46.1 (PHP 8.3.26)

- **Frontend**: Blade templates + Alpine.js 3.x  usikkert.json

- **Database**: MariaDB 10.6+ (MySQL kompatibel)

- **CSS**: Tailwind CSS (via Breeze)  godekilder.json- **Hemmeligheter**: Kun i `.env`, aldri sjekket inn- **[Tighten Co.](https://tighten.co)**

- **Build**: Vite 6.3.6

- **API Auth**: Laravel Sanctum 4.2.0  funksjoner.json

- **Testing**: PHPUnit (Laravel default)

- **Hosting**: Plesk med PHP-FPM 8.3  donetoday.json- **CSRF**: Aktivert globalt- **[WebReinvent](https://webreinvent.com/)**



---  risiko.json



## 📞 Kontaktscripts/- **HTTPS**: Tvunget i produksjon- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**



**Prosjekt**: Smartesider Live Dashboard    banned-words-check.sh             - Sjekker kode for banned ord

**Eier**: Smartesider.no  

**Opprettet**: 6. oktober 2025  ```- **Rate-limiting**: 60 req/min per bruker- **[64 Robots](https://64robots.com)**

**Status**: Fase 2 fullført, Fase 3+ under planlegging  



**Strenge regler**:

- ❌ Ingen mock-data## 🚀 Installasjon & Plesk Oppsett- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**

- ❌ Ingen placeholders

- ❌ Ingen "kommer snart"-meldinger

- ✅ Kun fungerende, testet kode

- ✅ AI-learned tracking av alt### 1. Plesk Konfigurasjon### Funksjons-hashing- **[Cyber-Duck](https://cyber-duck.co.uk)**



---- Document Root: `/var/www/vhosts/smartesider.no/nytt.smartesider.no/public`



## 📝 Lisens- PHP Version: 8.3 (PHP-FPM)Alle funksjoner merkes med:- **[DevSquad](https://devsquad.com/hire-laravel-developers)**



Dette er et proprietært prosjekt for Smartesider.no.- HTTPS: Tvunget (Let's Encrypt SSL)


```php- **[Jump24](https://jump24.co.uk)**

### 2. Miljøvariaber

```bash# START {hash} / Beskrivelse- **[Redberry](https://redberry.international/laravel/)**

cp .env.example .env

# Rediger .env med riktige verdier// kode her- **[Active Logic](https://activelogic.com)**

php artisan key:generate

```# SLUTT {hash}- **[byte5](https://byte5.de)**



### 3. Database```- **[OP.GG](https://op.gg)**

```bash

php artisan migrate

php artisan db:seed

```Hash registreres i `AI-learned/funksjoner.json` med avhengigheter og følgefeil.## Contributing



### 4. Cron (Plesk Scheduled Tasks)

Legg til i Plesk → Scheduled Tasks:

```## 🛠 Teknisk StackThank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

* * * * * php /var/www/vhosts/smartesider.no/nytt.smartesider.no/artisan schedule:run >> /dev/null 2>&1

```



### 5. Rettigheter- **Backend**: Laravel 11 (PHP 8.3)## Code of Conduct

```bash

chmod -R 755 storage bootstrap/cache- **Frontend**: Blade templates + Alpine.js

chown -R www-data:www-data storage bootstrap/cache

```- **Database**: MariaDB 10.6+ (MySQL kompatibel)In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).



## 📝 Kjøreregler- **Cache**: Database driver



1. **Start hver fase**: Les `AI-learned/*`, kjør banned-ordsjekk- **Jobs**: Laravel Scheduler (cron)## Security Vulnerabilities

2. **Underveis**: Logg hvert steg i `donetoday.json`

3. **Kode-merking**: Alle funksjoner med hash og oppføring i `funksjoner.json`- **Hosting**: Plesk (PHP-FPM 8.3)

4. **Avslutt**: Oppdater `fungerer.json`, `feil.json`, `usikkert.json`, `godekilder.json`

5. **STOPP-regel**: Hvis noe er uklart → stopp, skriv til `usikkert.json` og be om avklaringIf you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.



## ⚠️ Banned Ord (i kode)## 📁 Prosjektstruktur



Følgende ord er IKKE tillatt i kode-filer:## License

- "kommer snart", "her kommer"

- "TODO", "FIXME", "XXX"```

- "lorem", "mock data", "fake data"

- "will be added", "not implemented"app/The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).



Kjør `./scripts/banned-words-check.sh` før hver fase.  Support/Sys/ReadonlyCommand.php  - Sikker wrapper for OS-kommandoer

config/

## 🧪 Testing  dashboard.php                     - Dashboard-spesifikk konfig

AI-learned/                         - Læring og dokumentasjon

```bash  fungerer.json

# Kjør banned-ord sjekk  feil.json

./scripts/banned-words-check.sh  usikkert.json

  godekilder.json

# Test read-only wrapper  funksjoner.json

php artisan tinker  donetoday.json

>>> App\Support\Sys\ReadonlyCommand::run('cat /proc/loadavg');  risiko.json

scripts/

# Kjør enhetstester  banned-words-check.sh             - Sjekker kode for banned ord

php artisan test```

```

## 🚀 Installasjon

## 🔗 Kilder

1. Klon/kopier filer til server

- [Laravel 11 Dokumentasjon](https://laravel.com/docs/11.x)2. Sett opp `.env` (bruk `.env.example` som mal)

- [Plesk Obsidian Docs](https://docs.plesk.com/en-US/obsidian/)3. Kjør `composer install --no-dev`

- [Laravel Breeze](https://laravel.com/docs/11.x/starter-kits#laravel-breeze)4. Kjør `php artisan migrate`

5. Sett opp cron: `* * * * * php /path/to/artisan schedule:run -q`

## 👤 Kontakt

## 📝 Kjøreregler

Terje - terje@smartesider.no

1. **Start hver fase**: Les `AI-learned/*`, kjør banned-ordsjekk

---2. **Underveis**: Logg hvert steg i `donetoday.json`

3. **Kode-merking**: Alle funksjoner med hash og oppføring i `funksjoner.json`

**Status**: Fase 0 fullført ✅ | Klar for Fase 1 (Innlogging)4. **Avslutt**: Oppdater `fungerer.json`, `feil.json`, `usikkert.json`, `godekilder.json`

5. **STOPP-regel**: Hvis noe er uklart → stopp, skriv til `usikkert.json` og be om avklaring

## ⚠️ Banned Ord (i kode)

Følgende ord er IKKE tillatt i kode-filer:
- "kommer snart", "her kommer"
- "will be added", "not implemented"

Kjør `./scripts/banned-words-check.sh` før hver fase.

## 🔗 Kilder

- [Laravel 11 Dokumentasjon](https://laravel.com/docs/11.x)
- [Plesk Obsidian Docs](https://docs.plesk.com/en-US/obsidian/)

## 👤 Kontakt

Terje - terje@smartesider.no
