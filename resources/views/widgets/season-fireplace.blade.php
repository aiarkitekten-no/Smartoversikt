<div class="h-full flex flex-col p-2">
  <div class="flex items-center justify-between mb-2 text-white/90">
    <div class="font-semibold">🔥 Peis</div>
  </div>
  <div class="flex-1 rounded-lg relative overflow-hidden bg-gradient-to-b from-rose-800/50 to-orange-700/50">
    <svg viewBox="0 0 300 200" class="w-full h-full">
      <!-- Hearth -->
      <rect x="40" y="140" width="220" height="10" fill="#4b2e2e"/>
      <!-- Logs -->
      <rect x="120" y="130" width="60" height="8" rx="4" fill="#6b3f3a"/>
      <rect x="100" y="135" width="60" height="8" rx="4" fill="#6b3f3a"/>
      <rect x="140" y="135" width="60" height="8" rx="4" fill="#6b3f3a"/>
      <!-- Flame -->
      <g>
        <path d="M150 130 C140 120, 155 110, 150 90 C160 100, 165 110, 160 120 C170 115, 175 130, 150 130" fill="#f97316" opacity="0.9">
          <animate attributeName="d" dur="2s" repeatCount="indefinite"
             values="M150 130 C140 120, 155 110, 150 90 C160 100, 165 110, 160 120 C170 115, 175 130, 150 130;
                     M150 130 C145 120, 150 108, 148 92 C158 102, 166 110, 158 118 C168 116, 172 128, 150 130;
                     M150 130 C140 120, 155 110, 150 90 C160 100, 165 110, 160 120 C170 115, 175 130, 150 130"/>
        <div class="p-4 flex flex-col gap-3" x-data="seasonFireplace()" @keydown.window.ctrl.sh="addLog()">
          <div class="flex items-center justify-between">
            <div class="text-sm text-amber-300/90">Peiskos – trykk Legg på ved for mer varme</div>
            <div class="flex items-center gap-2">
              <button @click="addLog()" class="px-3 py-1.5 rounded bg-amber-600 hover:bg-amber-500 text-amber-50 text-sm shadow">
                Legg på ved
              </button>
            </div>
          </div>
          <div class="relative rounded-lg overflow-hidden ring-1 ring-amber-500/20 shadow-inner" style="background: radial-gradient(120% 80% at 50% 80%, rgba(251,191,36,0.10), rgba(17,24,39,1))">
            <canvas x-ref="fireCanvas" class="w-full h-56 block"></canvas>
            <div class="pointer-events-none absolute inset-0" style="box-shadow: inset 0 -30px 80px rgba(253,186,116,0.25), inset 0 20px 60px rgba(0,0,0,0.35)"></div>
          </div>
        </div>
