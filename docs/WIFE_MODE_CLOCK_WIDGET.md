# 💕 "Ventende Kone" Klokke Widget - Implementasjon

## 📋 Oversikt

Klokke-widgeten har nå en morsom "Wife Mode" som aktiveres på ettermiddagen/kvelden med eskalerende dramatikk basert på klokkeslettet.

## ⏰ Tidsbaserte Moods

### 15:30 - 16:00: **CALM** (Rolig) 😊💕
- **Bakgrunn**: Pastell rosa gradient med animasjon
- **Visuelt**: Flytende hjerter 💕
- **Melding**: "Alt er bra 💖"
- **Stemning**: Fredelig, alt er fint

### 16:00 - 16:30: **IMPATIENT** (Utålmodig) 🤔⏰
- **Bakgrunn**: Varmere rosa→oransje pulserende gradient
- **Visuelt**: Stor pulserende klokke ⏰
- **Melding**: "Husker du noe i dag? 🤔"
- **Stemning**: Litt nervøs, hint om at tiden går

### 16:30 - 17:00: **WORRIED** (Bekymret) 😐📱
- **Bakgrunn**: Oransje→rød gradient med shake-animasjon
- **Visuelt**: Telefon-ikon som rister 📱
- **Melding**: "HVOR ER DU??? 📱"
- **Stemning**: Ringer deg sikkert snart

### 17:00 - 18:00: **IRRITATED** (Irritert) 😤💐
- **Bakgrunn**: Dyp rød gradient med "stomp" animasjon
- **Visuelt**: Visne blomster 🥀 + flamme-partikler 🔥
- **Melding**: "Blomster hadde vært fint... 💐"
- **Stemning**: Sur, sarkastisk, men ikke helt på topp ennå
- **⭐ KNAPP AKTIVERES**: "💌 Send Unnskyldning SMS"

### 18:00 - 18:30: **FURIOUS** (Rasende) 🔥😈
- **Bakgrunn**: Mørkerød→svart "lava" gradient med pulserende glow + stomp
- **Visuelt**: Djevel 😈 + masse flammer 🔥🔥🔥
- **Melding**: "Sofaen er klar for deg 🛋️😈"
- **Stemning**: FULL KAOS - du sover på sofaen i natt!
- **⭐ KNAPP AKTIVERES**: "💌 Send Unnskyldning SMS"

### Etter 18:30
- Tilbake til normal klokke-visning (dag/natt/morgengry/kveldssol)

## 💌 SMS "Unnskyld"-Knapp

### Når aktiveres den?
Fra **17:00** og utover (IRRITATED & FURIOUS modes)

### Funksjonalitet
- Sender automatisk SMS til `4747487778`
- **30 forskjellige søte meldinger** som roterer
- Hver gang du trykker får du en ny melding
- Integrert med eksisterende SMStools API

### Eksempel-meldinger
1. "Hei kjære! ❤️ Tenker på deg. Kommer snart hjem! 😘"
2. "Du er verdens beste! 💕 Blir ikke seint i dag, love! 🏃‍♂️"
3. "Savner deg allerede! 💖 På vei hjem nå! 🚗"
4. "Du er min stjerne ⭐ Gleder meg til å se deg! 😍"
5. "Beklager forsinkelsen! 🙏 Kompenserer med klem når jeg kommer! 🤗"
... (totalt 30 ulike meldinger)

### UX
- **Før sending**: Rosa knapp "💌 Send Unnskyldning SMS"
- **Under sending**: Grå knapp med "⏳ Sender..."
- **Suksess**: Grønn melding med preview av sendt tekst
- **Feil**: Rød feilmelding
- Status forsvinner automatisk etter 5 sekunder

## 🎨 Animasjoner & Effekter

### CSS Animasjoner
- `heart-float`: Hjerter som flyter opp og forsvinner
- `flame-rise`: Flammer som stiger oppover
- `flower-wilt`: Blomster som visner
- `shake`: Risting (telefon/widget)
- `stomp`: "Fottramp" effekt (widget hopper)
- `pulse-glow`: Pulserende rød glød

### Visuelle Detaljer
- Gradient-bakgrunner med smooth overganger
- Partikkel-effekter (hjerter, flammer)
- Emoji-ikoner som matcher stemningen
- Backdrop blur på meldingsbokser for lesbarhet

## 🔧 Teknisk Implementasjon

### PHP (Blade)
```php
// Beregner mood basert på time + minutt
$timeValue = $currentHour * 100 + $currentMinute;
if ($timeValue >= 1530 && $timeValue < 1600) {
    $wifeMood = 'calm';
} // ... etc
```

### Alpine.js
```javascript
x-data="{
    sweetMessages: [ /* 30 meldinger */ ],
    messageIndex: 0,
    async sendApologySms() {
        // Roterer gjennom meldinger
        // Sender via /api/sms/send
    }
}"
```

### API Integration
- Bruker eksisterende `/api/sms/send` endpoint
- SMStools API backend
- CSRF-beskyttet
- Error handling med bruker-feedback

## 📱 Testing

### Manuell testing
1. Endre systemtiden til 17:30
2. Refresh widget
3. Se "IRRITATED" mood med blomster-hint
4. Klikk "Send Unnskyldning SMS"
5. Verifiser SMS sendt til 4747487778

### Tidsintervaller å teste
- `15:35` → Calm mode
- `16:15` → Impatient mode
- `16:45` → Worried mode
- `17:20` → Irritated mode + knapp
- `18:10` → Furious mode + knapp
- `19:00` → Tilbake til normal

## 🎯 Features

✅ 5 distinkte mood-levels med unik visuell stil  
✅ Tidsbasert automatisk aktivering (15:30-18:30)  
✅ 30 roterende søte SMS-meldinger  
✅ "Unnskyld"-knapp fra kl. 17:00  
✅ SMS integrert med eksisterende API  
✅ Smooth animasjoner og overganger  
✅ Responsiv feedback (loading, success, error)  
✅ Automatisk retur til normal visning etter 18:30  

## 💡 Humor-elementer

- **Blomster-hint**: Når det er for seint for bare unnskyldning
- **Sofa-trussel**: Klassisk "du sover på sofaen" når det er virkelig ille
- **Eskalerende emojis**: Fra 😊 til 😈
- **Visuell dramatikk**: Fra søte hjerter til apokalyptiske flammer
- **Varierte meldinger**: Aldri samme melding to ganger på rad

## 🚀 Fremtidige Forbedringer (valgfritt)

- [ ] Legg til lydeffekter (toggle on/off)
- [ ] "Blomsterbutikk"-link for emergencies
- [ ] Countdown: "Middag var klar for X minutter siden"
- [ ] Temp-o-meter visuell gauge
- [ ] Lagre statistikk: Antall unnskyldnings-SMS sendt
- [ ] SMS-historikk i widgeten

---

**Utviklet**: Oktober 2025  
**Humor Level**: 9001 😄  
**Wife Approval**: Pending... 😅
