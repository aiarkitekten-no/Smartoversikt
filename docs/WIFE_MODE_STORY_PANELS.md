# 🎬 Tonje's Ventende Tegneserie-Ruter

## 📋 Oversikt

Under "Oppdatert:"-feltet i klokke-widgeten vises nå en **daglig roterende tegneserie-rute** som forteller historien om Tonjes eskalerende utålmodighet når du er forsinket!

## 🔄 Rotasjonssystem

**13 forskjellige paneler** som roterer basert på dag i året:
- Hver dag viser et nytt panel
- Samme panel hele dagen
- Neste dag = nytt panel
- Roterer gjennom alle 13 varianter

## 🎨 De 13 Panelene

### 1️⃣ **Progressiv Countdown Bar**
```
⏳ Tonje venter:
[████████░░] 
125 minutter
```
- Grønn bar (0-60 min)
- Gul bar (60-120 min)
- Rød bar (120+ min)
- Viser eksakt ventetid

### 2️⃣ **Emoji Timeline**
```
😊    🤔    😐    😤    😈
15:30 16:00 16:30 17:00 18:00
```
- Visuell tidslinje med emojis
- Viser mood-progresjonen
- Kompakt oversikt

### 3️⃣ **Live Tonje-Tanker**
```
💭 "HVOR ER HAN?! 😤"
```
Mood-baserte tanker:
- **CALM**: "Han kommer snart 💕"
- **IMPATIENT**: "Hmm... hvor blir han av? 🤔"
- **WORRIED**: "Begynner å bli bekymret... 😐"
- **IRRITATED**: "HVOR ER HAN?! 😤"
- **FURIOUS**: "Sofaen venter... 😈"

### 5️⃣ **Blomster-Meter**
```
Blomster trengs:
🌹🌹🌹🌹🌹
```
Eskalerer med mood:
- CALM: 🌹 (1 rose)
- IMPATIENT: 🌹🌹 (2 roser)
- WORRIED: 🌹🌹🌹 (3 roser)
- IRRITATED: 🌹🌹🌹🌹🌹 (5 roser)
- FURIOUS: 💐💐💐 (3 buketter!)

### 7️⃣ **Temperatur-Gauge**
```
🌡️ Kokende
[████████████████████] 80%
```
Gradient fra blå → gul → rød:
- CALM: 20% (Rolig)
- IMPATIENT: 40% (Varm)
- WORRIED: 60% (Het)
- IRRITATED: 80% (Kokende)
- FURIOUS: 100% (LAVA!)

### 9️⃣ **Tapping Foot Animation**
```
    👠
*tap tap tap*
```
Animasjonshastighet øker:
- CALM: Ingen animasjon
- IMPATIENT: Pulse
- WORRIED: Bounce
- IRRITATED: Ping (rask)
- FURIOUS: Ping (raskere)

### 🔟 **Clock Watching Counter**
```
    47x
Tonje har sjekket klokka
```
- Beregner 2 sjekker per minutt
- Maks 99 for visning
- Økende tall = økende nervøsitet

### 1️⃣1️⃣ **Phone Check Tracker**
```
📱 Sjekket: 23x    ☎️ Ring: 3x
```
- Telefon sjekkes 1.5x per minutt
- 1 anrop per 30 min ventetid
- To-kolonners statistikk

### 1️⃣2️⃣ **Food Status**
```
🍝 Middag: ❄️ Kald
```
Matens tilstand:
- **CALM**: 🔥 Varmes opp
- **IMPATIENT**: 🌡️ Varm
- **WORRIED**: 🌡️ Lunken
- **IRRITATED**: ❄️ Kald
- **FURIOUS**: 🗑️ I søpla

### 1️⃣4️⃣ **Flower Shop Alert**
```
🌹 Blomsterbutikk
⏰ Stenger om 23 min!
```
- Countdown til stengetid (18:00)
- Rød tekst når det haster
- Etter 18:00: "STENGT! 😱"

### 1️⃣5️⃣ **Live Tonje-Quotes**
```
Tonje sier:
"Blomster? BLOMSTER?!" 😤
```
Mood-baserte sitater:
- **CALM**: "Har du glemt noe?" 🤨
- **IMPATIENT**: "Trafikken kan da ikke være SÅ ille..." 🙄
- **WORRIED**: "Hvorfor svarer du ikke?!" 😰
- **IRRITATED**: "Blomster? BLOMSTER?!" 😤
- **FURIOUS**: "Sofaen er myk i år!" 😈

### 1️⃣6️⃣ **Sarkastisk Protips**
```
💡 Protip: Sofaen har WiFi
```
Mood-baserte tips:
- **CALM**: Alt er fortsatt bra
- **IMPATIENT**: En melding hadde vært greit
- **WORRIED**: Blomster selges hos Rema
- **IRRITATED**: "Beklager" er et godt ord
- **FURIOUS**: Sofaen har WiFi

### 1️⃣7️⃣ **Forsinkelses-Counter**
```
Du sa: 16:00
Klokka: 17:45
⚠️ 1t 45min FORSINKET
```
- Sammenligner forventet (16:00) med nå
- Beregner eksakt forsinkelse
- Rød advarsel når forsinket
- Grønn "✅ I rute!" hvis før 16:00

## 🎯 Teknisk Implementasjon

### Rotasjonslogikk
```php
$dayOfYear = date('z'); // 0-365
$selectedPanelKey = $availablePanels[$dayOfYear % count($availablePanels)];
```

### Panel-struktur
```php
$storyPanels = [
    1 => function($wifeMood, $currentHour, $currentMinute) {
        // Panel logikk her
        return $html;
    },
    // ... osv
];
```

### Styling
- Hvit bakgrunn med 20% opacity
- Backdrop blur for glasseffekt
- Hvit border med 30% opacity
- Padding og avrundede hjørner
- Z-index 10 (over partikler)

## 📊 Eksempler per Mood

### CALM (15:30-16:00)
- **Panel 1**: Grønn bar, 15 min
- **Panel 5**: 🌹 (1 rose)
- **Panel 7**: 🌡️ Rolig (20%)
- **Panel 12**: 🍝 Varmes opp

### IRRITATED (17:00-18:00)
- **Panel 1**: Rød bar, 90+ min
- **Panel 5**: 🌹🌹🌹🌹🌹 (5 roser!)
- **Panel 7**: 🌡️ Kokende (80%)
- **Panel 12**: 🍝 ❄️ Kald
- **Panel 17**: ⚠️ 1t FORSINKET

### FURIOUS (18:00-18:30)
- **Panel 1**: Rød bar, 150+ min
- **Panel 5**: 💐💐💐 (3 buketter)
- **Panel 7**: 🌡️ LAVA! (100%)
- **Panel 12**: 🍝 🗑️ I søpla
- **Panel 16**: 💡 Sofaen har WiFi

## 🧪 Testing

### Sjekk forskjellige dager:
```php
// Endre system-dato for å teste rotasjon
date('z') // Dag 0 → Panel X
date('z') // Dag 1 → Panel Y
date('z') // Dag 2 → Panel Z
```

### Sjekk forskjellige moods:
1. **15:35** → CALM mood
2. **16:15** → IMPATIENT mood
3. **17:20** → IRRITATED mood
4. **18:10** → FURIOUS mood

### Verifiser paneler:
- Refresh side → Se dagens panel
- Sjekk at innhold matcher mood
- Verifiser animasjoner fungerer
- Test blomsterbutikk countdown

## 🎨 Visuelt Design

```
┌─────────────────────────────────────┐
│  15:24:20                           │
│  onsdag, 8. oktober 2025            │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ 😤 Blomster hadde vært fint │   │
│  └─────────────────────────────┘   │
│                                     │
│  [Server Info Grid]                 │
│                                     │
│  Oppdatert: 2 sekunder siden        │
│                                     │
│  ┌─────────────────────────────┐   │
│  │   🌹 Blomsterbutikk         │   │ ← STORY PANEL
│  │   ⏰ Stenger om 23 min!     │   │
│  └─────────────────────────────┘   │
└─────────────────────────────────────┘
```

## 💡 Fordeler

✅ **Variasjon**: 13 forskjellige paneler  
✅ **Daglig rotasjon**: Nytt panel hver dag  
✅ **Kontekstuell**: Tilpasser seg mood  
✅ **Humoristisk**: Morsom storytelling  
✅ **Informativ**: Viser faktisk data  
✅ **Visuelt**: Emojis og farger  
✅ **Kompakt**: Passer i widget-området  

## 🚀 Fremtidige Forbedringer

Potensielle tillegg:
- [ ] Animerte overganger mellom paneler
- [ ] Flere panelvarianter (20+)
- [ ] Bruker-valgt favoritt panel
- [ ] Historikk: "I går ventet Tonje X minutter"
- [ ] Statistikk: "Gjennomsnittlig forsinkelse: Y min"

---

**Utviklet**: Oktober 2025  
**Antall paneler**: 13  
**Rotasjonstype**: Daglig (basert på dag i året)  
**Humor Level**: Maximum! 😄
