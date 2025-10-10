<div class="h-full flex flex-col p-2">
  <div class="flex items-center justify-between mb-2 text-white/90">
    <div class="font-semibold">🎄 Juletrelys</div>
  </div>
  <div class="flex-1 rounded-lg relative overflow-hidden bg-gradient-to-b from-emerald-600/60 to-teal-700/50">
    <svg viewBox="0 0 300 200" class="w-full h-full">
      <defs>
        <style>
          .blink1 { animation: b1 2s linear infinite; }
          .blink2 { animation: b2 3s linear infinite; }
          @keyframes b1 { 0%,100% { opacity: .2 } 50% { opacity: 1 } }
          @keyframes b2 { 0%,100% { opacity: 1 } 50% { opacity: .2 } }
        </style>
      </defs>
      <!-- Tree -->
      <polygon points="150,20 190,80 170,80 210,120 90,120 130,80 110,80" fill="#134e4a"/>
      <rect x="145" y="120" width="10" height="20" fill="#4d3b2f"/>
      <!-- Lights -->
      <g fill="#ffd166">
        <circle class="blink1" cx="150" cy="50" r="4"/>
        <circle class="blink2" cx="170" cy="85" r="3"/>
        <circle class="blink1" cx="130" cy="85" r="3"/>
        <circle class="blink2" cx="190" cy="115" r="3"/>
        <circle class="blink1" cx="110" cy="115" r="3"/>
      </g>
    </svg>
  </div>
</div>
