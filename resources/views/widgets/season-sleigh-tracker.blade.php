<div class="h-full flex flex-col p-2">
  <div class="flex items-center justify-between mb-2 text-white/90">
    <div class="font-semibold">🛷 Nissens Radar</div>
  </div>
  <div class="flex-1 rounded-lg relative overflow-hidden bg-gradient-to-b from-slate-800/70 to-sky-900/60">
    <svg viewBox="0 0 300 200" class="w-full h-full">
      <!-- Path -->
      <path id="route" d="M10,150 C80,20 220,20 290,150" fill="none" stroke="#94a3b8" stroke-dasharray="4 4"/>
      <!-- Sleigh -->
      <g>
        <circle r="4" fill="#eab308">
          <animateMotion dur="10s" repeatCount="indefinite" rotate="auto">
            <mpath href="#route"/>
          </animateMotion>
        </circle>
        <circle r="2" fill="#eab308" opacity="0.6">
          <animateMotion dur="10s" repeatCount="indefinite" begin="-0.2s" rotate="auto">
            <mpath href="#route"/>
          </animateMotion>
        </circle>
      </g>
    </svg>
  </div>
</div>
