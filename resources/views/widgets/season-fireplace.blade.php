<div class="h-full flex flex-col p-3" x-data="seasonFireplace()" @keydown.window.ctrl.l="addLog()">
  <div class="flex items-center justify-between mb-2">
    <div class="font-semibold text-slate-800">🔥 Peis</div>
    <div class="flex items-center gap-2">
      <button @click="addLog()" class="px-3 py-1.5 rounded bg-amber-600 hover:bg-amber-500 text-amber-50 text-sm shadow">
        Legg på ved
      </button>
      <button @click="toggleAudio()" class="px-3 py-1.5 rounded bg-slate-700 hover:bg-slate-600 text-slate-100 text-sm shadow">
        <span x-show="!audioOn">Skru på knitring</span>
        <span x-show="audioOn">Skru av knitring</span>
      </button>
    </div>
  </div>
</div>
  <div class="flex-1">
    <div x-ref="fireFrame" class="relative rounded-lg overflow-hidden ring-1 ring-amber-500/20 shadow-inner" style="background: radial-gradient(120% 80% at 50% 80%, rgba(251,191,36,0.10), rgba(17,24,39,1))">
      <canvas x-ref="fireCanvas" class="w-full block" style="height:14rem; min-height:14rem;"></canvas>
      <div x-ref="fireGlow" class="pointer-events-none absolute inset-0" style="box-shadow: inset 0 -30px 80px rgba(253,186,116,0.25), inset 0 20px 60px rgba(0,0,0,0.35)"></div>
      <div x-show="!ctx" class="absolute inset-0 grid place-items-center text-amber-200/70 text-sm">
        Peisen din krever Canvas-støtte.
      </div>
    </div>
  </div>
