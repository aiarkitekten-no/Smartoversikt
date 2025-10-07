# Settings System - README

## Oversikt

Settings-systemet støtter nå **flere IMAP/SMTP-kontoer** lagret i database, med krypterte passord og full CRUD-funksjonalitet.

## Nye Funksjoner

### 1. **Database-baserte Mail-kontoer**
- IMAP og SMTP-kontoer lagres i `mail_accounts` tabell
- Passord krypteres automatisk med Laravel encryption
- Støtte for ubegrenset antall kontoer
- Aktiv/inaktiv status per konto

### 2. **Settings-side: /settings**
- Legg til nye IMAP-kontoer (+ Legg til IMAP knapp)
- Rediger eksisterende kontoer (inline rediger-skjema)
- Slett kontoer med bekreftelse
- Vær-innstillinger (lat/lon/lokasjon)

### 3. **IMAP Widget Oppdateringer**
- Bruker automatisk første aktive IMAP-konto fra database
- Viser kontonavn i widget
- Fallback til "ikke konfigurert" hvis ingen kontoer

## Bruk

### Legge til IMAP-konto:
1. Gå til **Innstillinger** (brukermenyen øverst høyre)
2. Klikk **+ Legg til IMAP** under "IMAP Kontoer"
3. Fyll inn:
   - **Navn**: F.eks "Terje - Smartesider" (for identifikasjon)
   - **Server**: mail.smartesider.no
   - **Port**: 993 (SSL) eller 143 (TLS)
   - **Brukernavn**: din@epost.no
   - **Passord**: ditt passord
   - **Kryptering**: SSL (anbefalt for 993), TLS for 143
   - **Valider SSL-sertifikat**: Ja (anbefalt)
   - **Aktiv**: Ja
4. Klikk **Legg til konto**

### Redigere konto:
1. Klikk **Rediger** på ønsket konto
2. Endre felter (passord kan stå tomt for å beholde eksisterende)
3. Klikk **Lagre**

### Slette konto:
1. Klikk **Slett** på ønsket konto
2. Bekreft sletting

## Database Schema

```sql
CREATE TABLE mail_accounts (
    id INTEGER PRIMARY KEY,
    name VARCHAR(255),           -- Display name
    type VARCHAR(255),           -- 'imap' or 'smtp'
    host VARCHAR(255),           -- Mail server
    port INTEGER,                -- Port number
    username VARCHAR(255),       -- Email/username
    password VARCHAR(255),       -- Encrypted password
    encryption VARCHAR(255),     -- 'ssl', 'tls', 'none'
    validate_cert BOOLEAN,       -- Validate SSL cert
    is_active BOOLEAN,           -- Active status
    check_interval INTEGER,      -- Seconds between checks
    metadata TEXT,               -- JSON for extra settings
    created_at DATETIME,
    updated_at DATETIME
);
```

## API Routes

```php
GET    /settings                              // Settings page
POST   /settings/mail-accounts                // Create mail account
PATCH  /settings/mail-accounts/{id}           // Update mail account
DELETE /settings/mail-accounts/{id}           // Delete mail account
PATCH  /settings/weather                      // Update weather settings
```

## Fremtidige forbedringer

- [ ] Widget-spesifikk konto-valg (velg hvilken konto hver widget skal bruke)
- [ ] Multi-mailbox støtte (INBOX, Sent, Drafts etc.)
- [ ] Test-tilkobling knapp før lagring
- [ ] SMTP-konto funksjonalitet (send e-post fra widgets)
- [ ] Statistikk per konto (sist sjekket, antall feil)
- [ ] Bulk-import fra .env

## Migrasjon fra .env

Eksisterende IMAP/SMTP-innstillinger i `.env` fungerer fortsatt som fallback, men anbefales å migrere til database:

```bash
# Gammel metode (deprecated):
IMAP_HOST=mail.smartesider.no
IMAP_PORT=993
IMAP_USERNAME=terje@smartesider.no
IMAP_PASSWORD=passord

# Ny metode: Bruk Settings-siden i webgrensesnittet
```

## Sikkerhet

- Passord krypteres med Laravel `encrypt()` (AES-256-CBC)
- APP_KEY må være satt i `.env` for kryptering
- Passord vises aldri i klartekst etter lagring
- CSRF-beskyttelse på alle POST/PATCH/DELETE
