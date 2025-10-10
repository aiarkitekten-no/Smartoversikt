<div class="h-full flex flex-col p-2">
  <div class="flex items-center justify-between mb-2 text-white/90">
    <div class="font-semibold">❄️ Snøkule</div>
  </div>
  <div class="flex-1 rounded-lg relative overflow-hidden bg-gradient-to-b from-indigo-500/60 to-purple-600/50">
    <svg viewBox="0 0 300 200" class="w-full h-full">
      <defs>
        <radialGradient id="glow" cx="50%" cy="35%" r="60%">
          <stop offset="0%" stop-color="#ffffff" stop-opacity="0.6"/>
          <stop offset="100%" stop-color="#ffffff" stop-opacity="0"/>
        </radialGradient>
      </defs>
      <!-- Base -->
      <rect x="70" y="160" width="160" height="20" rx="10" fill="#3b2f5a"/>
      <!-- Glass -->
      <circle cx="150" cy="110" r="70" fill="url(#glow)"/>
      <circle cx="150" cy="110" r="68" fill="rgba(255,255,255,0.2)"/>
      <!-- Snow particles -->
      <g>
        <circle cx="140" cy="80" r="2" fill="#fff">
          <animate attributeName="cy" from="70" to="160" dur="6s" repeatCount="indefinite"/>
          <animate attributeName="cx" values="140;150;145;155;140" dur="6s" repeatCount="indefinite"/>
        </circle>
        <circle cx="160" cy="70" r="1.8" fill="#fff">
          <animate attributeName="cy" from="60" to="160" dur="5s" repeatCount="indefinite"/>
          <animate attributeName="cx" values="160;150;165;145;160" dur="5s" repeatCount="indefinite"/>
        </circle>
        <circle cx="120" cy="85" r="1.5" fill="#fff">
          <animate attributeName="cy" from="75" to="160" dur="7s" repeatCount="indefinite"/>
          <animate attributeName="cx" values="120;130;115;140;120" dur="7s" repeatCount="indefinite"/>
        </circle>
      </g>
      <!-- House silhouette -->
      <rect x="130" y="120" width="40" height="20" fill="#4b3a6f"/>
      <polygon points="150,100 180,120 120,120" fill="#5b478a"/>
      <rect x="146" y="128" width="8" height="12" fill="#fffb"/>
    </svg>
  </div>
</div>
