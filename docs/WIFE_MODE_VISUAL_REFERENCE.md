# 🎨 Wife Mode - Visuell Referanse

## Mood States Oversikt

```
┌─────────────────────────────────────────────────────────────┐
│  15:30-16:00  │  CALM  😊💕                                  │
├─────────────────────────────────────────────────────────────┤
│  Bakgrunn:    │  Pastell rosa → lilla gradient (animated)   │
│  Effekt:      │  Flytende hjerter 💕💕💕                      │
│  Melding:     │  "Alt er bra 💖"                             │
│  Knapp:       │  Nei                                         │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  16:00-16:30  │  IMPATIENT  🤔⏰                             │
├─────────────────────────────────────────────────────────────┤
│  Bakgrunn:    │  Oransje-rosa pulserende                    │
│  Effekt:      │  Stor klokke ⏰ (pulse)                      │
│  Melding:     │  "Husker du noe i dag? 🤔"                   │
│  Knapp:       │  Nei                                         │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  16:30-17:00  │  WORRIED  😐📱                               │
├─────────────────────────────────────────────────────────────┤
│  Bakgrunn:    │  Oransje → rød (shaking!)                   │
│  Effekt:      │  Telefon 📱 rister voldsomt                  │
│  Melding:     │  "HVOR ER DU???"                             │
│  Knapp:       │  Nei                                         │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  17:00-18:00  │  IRRITATED  😤💐                             │
├─────────────────────────────────────────────────────────────┤
│  Bakgrunn:    │  Dyp rød gradient (stomping animation!)     │
│  Effekt:      │  Visne blomster 🥀 + flammer 🔥🔥🔥           │
│  Melding:     │  "Blomster hadde vært fint... 💐"            │
│  Knapp:       │  JA! 💌 "Send Unnskyldning SMS"              │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  18:00-18:30  │  FURIOUS  🔥😈                               │
├─────────────────────────────────────────────────────────────┤
│  Bakgrunn:    │  Mørkerød → svart LAVA (glowing + stomp!)   │
│  Effekt:      │  Djevel 😈 + masse flammer 🔥🔥🔥🔥🔥🔥🔥       │
│  Melding:     │  "Sofaen er klar for deg 🛋️😈"              │
│  Knapp:       │  JA! 💌 "Send Unnskyldning SMS"              │
└─────────────────────────────────────────────────────────────┘
```

## SMS Meldinger (Sample 10 av 30)

```
1.  "Hei kjære! ❤️ Tenker på deg. Kommer snart hjem! 😘"
2.  "Du er verdens beste! 💕 Blir ikke seint i dag, love! 🏃‍♂️"
3.  "Savner deg allerede! 💖 På vei hjem nå! 🚗"
4.  "Du er min stjerne ⭐ Gleder meg til å se deg! 😍"
5.  "Beklager forsinkelsen! 🙏 Kompenserer med klem når jeg kommer! 🤗"
6.  "Takk for at du er så tålmodig! 💝 Du er gull verdt! ✨"
7.  "Vet du er den beste kona i verden? 👑 Kommer fort! 💨"
8.  "Skal vi bestille middag? 🍕 Min treat! 😊"
9.  "Du gjør hver dag bedre! 🌟 Snart hjemme! 🏡"
10. "Elsker deg mest! 💗 Bringer dessert med hjem! 🍰"

... (20 meldinger til)
```

## Color Palette

```css
/* CALM */
background: #FFC0CB → #FFB6C1 → #FFE4E1 (Pink Spectrum)

/* IMPATIENT */
background: #FFB88C → #FFA07A → #FF8C69 (Orange-Pink)

/* WORRIED */
background: #FF8C42 → #FF7F50 → #FF6347 (Orange-Red)

/* IRRITATED */
background: #DC143C → #B22222 → #8B0000 (Crimson → DarkRed)

/* FURIOUS */
background: #8B0000 → #4B0000 → #1a0000 (DarkRed → Black)
```

## Animasjon Timing

| Effect        | Duration | Easing          | Loop     |
|---------------|----------|-----------------|----------|
| heart-float   | 3s       | ease-in-out     | infinite |
| flame-rise    | 2s       | ease-in-out     | infinite |
| flower-wilt   | 3s       | ease-in-out     | forwards |
| shake         | 0.5s     | ease-in-out     | infinite |
| stomp         | 1s       | ease-in-out     | infinite |
| pulse-glow    | 1.5s     | ease-in-out     | infinite |

## Widget Layout

```
┌─────────────────────────────────────┐
│  🕐 Live Klokke          ⟳ ● ●     │  ← Header
├─────────────────────────────────────┤
│                                     │
│           [Particles/Icons]         │  ← Mood decorations
│                                     │
│         ╔═════════════════╗         │
│         ║    17:35:42     ║         │  ← Time display
│         ║  tirsdag, 8. okt ║         │
│         ╚═════════════════╝         │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ 😤 Blomster hadde vært fint │   │  ← Mood message
│  └─────────────────────────────┘   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │  💌 Send Unnskyldning SMS   │   │  ← SMS Button
│  └─────────────────────────────┘   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ ✅ Sendt! "Hei kjære! ❤️..."│   │  ← SMS Status
│  └─────────────────────────────┘   │
│                                     │
│  ┌─────────┬─────────┐             │
│  │ Server  │   PHP   │             │  ← Server info
│  │ Minne   │   Peak  │             │
│  └─────────┴─────────┘             │
│                                     │
│     Oppdatert: 2 sekunder siden    │  ← Footer
└─────────────────────────────────────┘
```

## Responsiv Oppførsel

- **Mobile**: Full bredde, stacked layout
- **Tablet**: 2-kolonne grid for server info
- **Desktop**: Optimalisert for widget-kort størrelse
- **Z-index**: Particles i bakgrunn, innhold i forgrunnen

## Accessibility

- ✅ Color contrast på tekst
- ✅ Backdrop blur for lesbarhet
- ✅ Disabled state på knapp under sending
- ✅ Clear loading states
- ✅ Error messages synlige og forståelige
- ⚠️ Mangler: Screen reader labels (kan legges til ved behov)

## Browser Support

| Feature          | Chrome | Firefox | Safari | Edge |
|------------------|--------|---------|--------|------|
| CSS Animations   | ✅     | ✅      | ✅     | ✅   |
| Backdrop Filter  | ✅     | ✅      | ✅     | ✅   |
| Fetch API        | ✅     | ✅      | ✅     | ✅   |
| Alpine.js 3.x    | ✅     | ✅      | ✅     | ✅   |
| Emoji Rendering  | ✅     | ✅      | ✅     | ✅   |

---

**Design Philosophy**: Humor + Functionality + Smooth UX  
**Target Audience**: Anyone with a waiting spouse 😄  
**Humor Rating**: 10/10 would use again
