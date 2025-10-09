# 💳 Forfall Widget - Dokumentasjon

## 📋 Oversikt

**Forfall-widgeten** holder oversikt over månedlige regninger og abonnementer med forfallsdatoer, automatisk fargesetting basert på hvor nær forfallet er, og beregning av totale utgifter.

---

## ✨ Implementerte Funksjoner

### **1. Betalt denne måned-toggle** ✓/✗
- ✅ Checkbox ved hver oppføring
- ✅ Klikk → marker som betalt
- ✅ Oppdaterer automatisk "Rest denne måned"
- ✅ Visuell feedback

### **8. Sortering etter forfallsdato**
- ✅ Nærmeste forfall øverst
- ✅ Automatisk sortering
- ✅ Basert på "dager igjen"

### **9. Progress bar til neste forfall**
- ✅ Visuell tidslinje per oppføring
- ✅ Fargekodet (rød/gul/grønn)
- ✅ 0-30 dager skala
- ✅ Smooth animasjoner

### **13. Gjeldende/Neste måned-toggle**
- ✅ Toggle-knapp: "📅 Denne" / "📅 Neste"
- ✅ Filtrerer automatisk basert på valg
- ✅ Smart visning av relevante forfall

---

## 🎨 Visuelt Design

### **Fargesystem (Urgency Levels)**

```
🔴 RØD    → Under 5 dager til forfall (KRITISK!)
🟡 GUL    → 5-7 dager til forfall (VIKTIG)
🟢 GRØNN  → Over 7 dager til forfall (OK)
```

### **Widget Layout**

```
┌─────────────────────────────────────┐
│  💳 Forfall    [📅 Denne]  [+ Ny]  │
├─────────────────────────────────────┤
│  ┌─────────────────────────────┐   │
│  │ 🔴 3 dg  Netflix            │   │
│  │ 149,00 kr            [✓] [×]│   │
│  │ [██████████░░░░] (30%)      │   │
│  │ Forfaller: 15.              │   │
│  └─────────────────────────────┘   │
│  ┌─────────────────────────────┐   │
│  │ 🟡 6 dg  Strøm              │   │
│  │ 1.250,00 kr          [ ] [×]│   │
│  │ [████████████░░] (50%)      │   │
│  │ Forfaller: 18.              │   │
│  └─────────────────────────────┘   │
│  ┌─────────────────────────────┐   │
│  │ 🟢 12 dg Husleie            │   │
│  │ 8.900,00 kr          [ ] [×]│   │
│  │ [██████████████] (70%)      │   │
│  │ Forfaller: 25.              │   │
│  └─────────────────────────────┘   │
├─────────────────────────────────────┤
│  Utgift/mnd totalt:  10.299,00 kr  │
│  Rest denne måned:   10.150,00 kr  │
│  Betalt: 1 / 3                      │
└─────────────────────────────────────┘
```

---

## 💾 Database Struktur

### **Tabell: `bills`**

```sql
id                  - Primary key
user_id             - Foreign key til users
name                - Navn (Netflix, Strøm, etc.)
amount              - Beløp (decimal 10,2)
due_day             - Forfallsdag i måneden (1-31)
is_paid_this_month  - Boolean (betalt/ikke betalt)
sort_order          - Manuell sortering (integer)
created_at          - Timestamp
updated_at          - Timestamp
```

---

## 🔌 API Endpoints

### **GET /api/bills**
Hent alle forfall for innlogget bruker

**Response:**
```json
{
  "success": true,
  "bills": [
    {
      "id": 1,
      "name": "Netflix",
      "amount": "149.00",
      "due_day": 15,
      "is_paid_this_month": false,
      ...
    }
  ]
}
```

### **POST /api/bills**
Opprett nytt forfall

**Request:**
```json
{
  "name": "Netflix",
  "amount": 149.00,
  "due_day": 15
}
```

### **POST /api/bills/{id}/toggle-paid**
Toggle betalt-status

**Response:**
```json
{
  "success": true,
  "message": "Markert som betalt",
  "is_paid": true
}
```

### **PUT /api/bills/{id}**
Oppdater forfall

**Request:**
```json
{
  "name": "Netflix Premium",
  "amount": 179.00,
  "due_day": 15
}
```

### **DELETE /api/bills/{id}**
Slett forfall

**Response:**
```json
{
  "success": true,
  "message": "'Netflix' slettet"
}
```

---

## 🎯 Funksjoner i Detalj

### **1. Betalt-Toggle**

**Hvordan det fungerer:**
- Checkbox ved hver oppføring
- Klikk → API-kall til `/api/bills/{id}/toggle-paid`
- Instant feedback
- Oppdaterer "Rest denne måned" automatisk

**Logikk:**
```javascript
async togglePaid(billId) {
    await fetch(`/api/bills/${billId}/toggle-paid`, {
        method: 'POST',
        ...
    });
    await this.fetchData(); // Refresh
}
```

### **8. Sortering**

**Automatisk sortering:**
```javascript
getFilteredBills() {
    return this.data.bills
        .filter(...)
        .sort((a, b) => a.days_until_due - b.days_until_due);
}
```

**Resultat:**
- Nærmeste forfall alltid øverst
- Kritiske (røde) vises først
- Viktige (gule) i midten
- OK (grønne) nederst

### **9. Progress Bar**

**Beregning:**
```javascript
getProgressWidth(days) {
    return Math.min(100, (days / 30) * 100);
}
```

**Visuelt:**
- 0 dager = 0% (tom bar)
- 15 dager = 50% (halvfull)
- 30+ dager = 100% (full)
- Farge matcher urgency level

### **13. Måned-Toggle**

**Filtrering:**
```javascript
if (showThisMonth) {
    return bill.due_day >= today || bill.days_until_due <= 7;
} else {
    return bill.due_day < today && bill.days_until_due > 7;
}
```

**Logikk:**
- **Denne måned**: Forfall som ikke har vært ennå + nært forestående
- **Neste måned**: Forfall som har passert denne måneden

---

## 📊 Automatiske Beregninger

### **Utgift/mnd totalt**
```php
$totalMonthly = $bills->sum('amount');
```
Sum av ALLE oppføringer

### **Rest denne måned**
```php
$remainingThisMonth = $bills
    ->where('is_paid_this_month', false)
    ->sum('amount');
```
Sum av kun UBETALTE oppføringer

### **Dager til forfall**
```php
public function getDaysUntilDueAttribute(): int
{
    $today = Carbon::today();
    $currentDay = $today->day;
    $dueDay = $this->due_day;
    
    if ($dueDay >= $currentDay) {
        // Denne måneden
        $dueDate = Carbon::create($today->year, $today->month, $dueDay);
    } else {
        // Neste måned
        $nextMonth = $today->copy()->addMonth();
        $dueDate = Carbon::create($nextMonth->year, $nextMonth->month, $dueDay);
    }
    
    return $today->diffInDays($dueDate, false);
}
```

---

## 🎬 Brukerveiledning

### **Legge til nytt forfall:**
1. Klikk **"+ Ny"**
2. Fyll inn:
   - **Navn**: f.eks. "Netflix"
   - **Beløp**: f.eks. "149.00"
   - **Forfallsdag**: f.eks. "15" (15. i måneden)
3. Klikk **"Lagre"**

### **Markere som betalt:**
1. Finn oppføringen i listen
2. Klikk på **checkbox** (✓)
3. Automatisk oppdatering av totaler

### **Slette forfall:**
1. Finn oppføringen
2. Klikk på **"×"** (rød knapp)
3. Bekreft sletting

### **Bytte mellom måneder:**
1. Klikk **"📅 Denne"** for å se neste måned
2. Klikk **"📅 Neste"** for å gå tilbake

---

## 🔍 Eksempel-data

```json
{
  "timestamp": "2025-10-09T08:05:32+02:00",
  "bills": [
    {
      "id": 1,
      "name": "Netflix",
      "amount": "149.00",
      "formatted_amount": "149,00 kr",
      "due_day": 15,
      "is_paid_this_month": false,
      "days_until_due": 6,
      "urgency_level": "yellow"
    },
    {
      "id": 2,
      "name": "Strøm",
      "amount": "1250.00",
      "formatted_amount": "1 250,00 kr",
      "due_day": 5,
      "is_paid_this_month": true,
      "days_until_due": 26,
      "urgency_level": "green"
    }
  ],
  "totals": {
    "monthly_total": 1399.00,
    "formatted_monthly_total": "1 399,00 kr",
    "remaining_this_month": 149.00,
    "formatted_remaining": "149,00 kr",
    "paid_count": 1,
    "total_count": 2
  }
}
```

---

## ✅ Testing Checklist

### **Grunnleggende funksjoner:**
- [ ] Legg til nytt forfall
- [ ] Se forfall i listen
- [ ] Marker som betalt (checkbox)
- [ ] Slett forfall
- [ ] Toggle mellom måneder

### **Beregninger:**
- [ ] Verifiser "Utgift/mnd totalt"
- [ ] Verifiser "Rest denne måned"
- [ ] Sjekk at betalt-count er korrekt

### **Visuelt:**
- [ ] Rød badge for < 5 dager
- [ ] Gul badge for 5-7 dager
- [ ] Grønn badge for > 7 dager
- [ ] Progress bar vises korrekt

### **Sortering:**
- [ ] Nærmeste forfall øverst
- [ ] Automatisk oppdatering

---

## 🚀 Fremtidige Forbedringer

Potensielle tillegg (ikke implementert ennå):
- [ ] Kategori-ikoner (🏠⚡📺)
- [ ] Notis-felt
- [ ] URL til leverandør
- [ ] E-post/SMS varsler
- [ ] Betalingshistorikk
- [ ] Statistikk og grafer
- [ ] Import fra CSV
- [ ] Gjentakende automatisk oppdatering

---

## 📁 Filer Opprettet

✅ `/database/migrations/2025_10_09_080532_create_bills_table.php`  
✅ `/app/Models/Bill.php`  
✅ `/app/Services/Widgets/ToolsBillsFetcher.php`  
✅ `/app/Http/Controllers/Api/BillsController.php`  
✅ `/resources/views/widgets/tools-bills.blade.php`  
✅ `/routes/api.php` (oppdatert)  
✅ `/config/widgets.php` (oppdatert)  

---

**Status**: ✅ Fullstendig implementert  
**Utviklet**: 9. Oktober 2025  
**Kategori**: Tools  
**Refresh interval**: 5 minutter
