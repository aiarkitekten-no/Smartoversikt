@php
    // Calculate overall CPU status based on average core usage
    $avgCoreUsage = 0;
    if (isset($data['cpu']['per_core']) && count($data['cpu']['per_core']) > 0) {
        $avgCoreUsage = array_sum(array_column($data['cpu']['per_core'], 'usage')) / count($data['cpu']['per_core']);
    }
    
    if ($avgCoreUsage < 30) {
        $coreStatus = 'excellent'; // Green
    } elseif ($avgCoreUsage < 60) {
        $coreStatus = 'warning'; // Yellow
    } else {
        $coreStatus = 'critical'; // Red
    }
@endphp

<style>
    .cores-excellent {
        background: linear-gradient(135deg, #10B981 0%, #34D399 50%, #6EE7B7 100%);
    }
    
    .cores-warning {
        background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 50%, #FCD34D 100%);
    }
    
    .cores-critical {
        background: linear-gradient(135deg, #DC2626 0%, #EF4444 50%, #F87171 100%);
    }
    .chat-box { max-height: 120px; overflow: auto; scrollbar-width: thin; }
    .chat-line { font-size: 11px; line-height: 1.2; }
    .badge { font-size: 10px; padding: 2px 6px; border-radius: 9999px; background: rgba(255,255,255,0.25); }
</style>

<div 
    x-data="(() => {
        // Get original widget object and capture its init before merging
        const widget = widgetData('system.cpu-cores');
        const origInit = widget?.init;
        // Small helper for names
        const nick = (i) => ({
            0:'Zen',1:'Turbo',2:'Byte',3:'Flux',4:'Nova',5:'Quark',6:'Ion',7:'Echo',8:'Bolt',9:'Glimt',10:'Krypto',11:'Pulse'
        })[i%12] || `Core-${i+1}`;
        const state = {
            // UI additions
            chat: [], chatPaused: false, maxChat: 30,
            leader: null, onDuty: [], prevCores: null,
            prevLoad1: null, prevLoad5: null,
            addChat(line){
                this.chat.push({ t: Date.now(), ...line });
                if (this.chat.length > this.maxChat) this.chat.splice(0, this.chat.length - this.maxChat);
                this.$nextTick(() => {
                    const el = this.$refs.coreChat; if (!el) return;
                    const nearBottom = (el.scrollTop + el.clientHeight + 24) >= el.scrollHeight;
                    if (!this.chatPaused && nearBottom) el.scrollTop = el.scrollHeight;
                });
            },
            coreName(i){ return `K${i+1} “${nick(i)}”`; },
            coreShort(i){ return `K${i+1}`; },
            onDataChange(newVal){
                const cores = newVal?.cpu?.per_core || [];
                // Leader & duty (høyest bruk nå)
                const sorted = [...cores].sort((a,b)=>b.usage-a.usage);
                this.leader = sorted[0]?.core ?? null;
                this.onDuty = sorted.slice(0,3).map(c=>c.core);

                // Queue gossip thresholds
                const l1 = newVal?.cpu?.loadavg?.['1min_percent'] ?? null;
                const l5 = newVal?.cpu?.loadavg?.['5min_percent'] ?? null;
                const cross = (prev, cur, th) => (prev !== null && ((prev < th && cur >= th) || (prev >= th && cur < th)));
                if (l1 !== null && this.prevLoad1 !== null) {
                    if (cross(this.prevLoad1, l1, 60)) this.addChat({ type:'system', msg:`Køvarsel: 1min ${l1.toFixed(0)}% (60% terskel passert)` });
                    if (cross(this.prevLoad1, l1, 80)) this.addChat({ type:'system', msg:`ALERT: 1min ${l1.toFixed(0)}% (80% terskel)` });
                }
                if (l5 !== null && this.prevLoad5 !== null) {
                    if (cross(this.prevLoad5, l5, 50)) this.addChat({ type:'system', msg:`Trend: 5min ${l5.toFixed(0)}% rundt 50%` });
                }
                this.prevLoad1 = l1; this.prevLoad5 = l5;

                // Core based chatter
                if (this.prevCores) {
                    cores.forEach(c => {
                        const prev = this.prevCores.find(p=>p.core===c.core)?.usage ?? null;
                        if (prev === null) return;
                        const name = this.coreShort(c.core);
                        const nickn = nick(c.core);
                        const delta = c.usage - prev;
                        // Crossing states
                        const prevBand = prev<20?0:prev<60?1:2;
                        const curBand = c.usage<20?0:c.usage<60?1:2;
                        if (curBand>prevBand) {
                            if (curBand===1) this.addChat({ author:name, msg:`${nickn}: Tar en liten jobb (opp fra idle)` });
                            if (curBand===2) this.addChat({ author:name, msg:`${nickn}: HOT HOT! trenger assistanse` });
                        } else if (curBand<prevBand) {
                            if (curBand===1) this.addChat({ author:name, msg:`${nickn}: Ned fra turbo, stabil nå` });
                            if (curBand===0) this.addChat({ author:name, msg:`${nickn}: Ferdig! tilbake i zen` });
                        } else {
                            // Large jump up/down
                            if (delta >= 25) this.addChat({ author:name, msg:`${nickn}: Fikk nye tasks (+${delta.toFixed(0)}%)` });
                            if (delta <= -25) this.addChat({ author:name, msg:`${nickn}: Slappet av (−${Math.abs(delta).toFixed(0)}%)` });
                        }
                    });
                } else {
                    // First load greeting
                    cores.slice(0,3).forEach(c=>this.addChat({ author:this.coreShort(c.core), msg:`${nick(c.core)} sier hei!` }));
                }
                this.prevCores = cores.map(c=>({core:c.core, usage:c.usage}));
            },
            coffeeBreak(core){
                const usage = this.data?.cpu?.per_core?.find(c=>c.core===core)?.usage ?? 100;
                if (usage < 20) this.addChat({ author:this.coreShort(core), msg:`${nick(core)} tok en kaffe ☕ (5s)` });
                else this.addChat({ author:this.coreShort(core), msg:`${nick(core)}: Ikke nå! midt i en task` });
            },
            init(){
                // Call original widget init (if available) exactly once
                if (typeof origInit === 'function') origInit.call(this);
                this.$watch('data', (nv)=>{ if(nv) this.onDataChange(nv); });
            }
        };
        // Merge without mutating the original widget object to avoid overwriting its init
        return Object.assign({}, widget, state);
    })()"
    class="widget-card cores-{{ $coreStatus }} rounded-lg shadow-lg overflow-hidden border-2 border-white/20"
>
    <!-- Compact Header -->
    <div class="p-2 border-b border-white/20 bg-black/20">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-gray-900 drop-shadow-[0_2px_4px_rgba(255,255,255,0.9)]">
                💻 CPU (<span x-text="data?.cpu?.cores || 12"></span>)
            </h3>
            <div class="text-[10px] text-gray-800/80 drop-shadow-md" x-text="formatTimestamp(lastUpdate)"></div>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="p-4 text-center">
        <svg class="animate-spin h-4 w-4 text-white mx-auto" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

    <!-- Error State -->
    <div x-show="error && !loading" class="p-4 text-center bg-black/20">
        <p class="text-[10px] text-white/80">⚠️ Feil</p>
    </div>

    <!-- Content - Ultra Compact Grid -->
    <div x-show="!loading && !error" class="p-2">
        <!-- Leader / duty row -->
        <div class="flex items-center justify-between mb-1 text-[10px] text-white/90">
            <div>
                <span class="badge" x-show="leader !== null">Shift leader: <span x-text="'K'+((leader??0)+1)"></span></span>
            </div>
            <div x-show="onDuty.length" class="opacity-90">På vakt: <span x-text="onDuty.map(n=>'K'+(n+1)).join(', ')"></span></div>
        </div>
        <div class="grid grid-cols-4 gap-1">
            <template x-for="core in data?.cpu?.per_core || []" :key="core.core">
                <div class="bg-black/30 rounded p-1 border border-white/20 hover:bg-black/40 transition-all text-center cursor-pointer" @click="coffeeBreak(core.core)">
                    <div class="text-[9px] font-bold text-gray-900 drop-shadow-[0_1px_2px_rgba(255,255,255,0.8)]">
                        <span x-text="'K'+(core.core+1)"></span>
                    </div>
                    <div class="text-sm font-bold drop-shadow-[0_1px_2px_rgba(255,255,255,0.9)]"
                         :class="core.usage < 30 ? 'text-green-900' : (core.usage < 60 ? 'text-yellow-900' : 'text-red-900')">
                        <span x-text="core.usage.toFixed(0)"></span>%
                    </div>
                    <div class="text-[10px]">
                        <span x-show="core.usage < 30">😴</span>
                        <span x-show="core.usage >= 30 && core.usage < 60">💼</span>
                        <span x-show="core.usage >= 60">🔥</span>
                        <div class="text-[9px] text-white/80" x-text="'“'+((()=>{const n=core.core;const names={0:'Zen',1:'Turbo',2:'Byte',3:'Flux',4:'Nova',5:'Quark',6:'Ion',7:'Echo',8:'Bolt',9:'Glimt',10:'Krypto',11:'Pulse'};return names[n%12]||('Core-'+(n+1));})())+'”'"></div>
                    </div>
                </div>
            </template>
        </div>
        <!-- Chat feed -->
        <div class="mt-2 bg-black/20 border border-white/20 rounded p-2 chat-box" x-ref="coreChat" @mouseenter="chatPaused=true" @mouseleave="chatPaused=false" aria-live="polite">
            <template x-for="(line, idx) in chat" :key="line.t + '-' + idx">
                <div class="chat-line" :class="{
                    'text-white' : line.type==='system',
                    'text-green-200' : line.author && line.msg && /zen|idle|slapp/.test(line.msg.toLowerCase()),
                    'text-yellow-200' : line.author && line.msg && /stabil|liten/.test(line.msg.toLowerCase()),
                    'text-red-200' : line.author && line.msg && /hot|alert|assist/.test(line.msg.toLowerCase())
                }">
                    <template x-if="line.author">
                        <span><strong x-text="line.author"></strong>: </span>
                    </template>
                    <span x-text="line.msg"></span>
                </div>
            </template>
            <template x-if="chat.length===0">
                    <div class="text-[10px] text-white/70">Kjernene gjør seg klare…</div>
            </template>
        </div>
    </div>

    <script>
        function formatTimestamp(timestamp) {
            if (!timestamp) return '';
            const date = new Date(timestamp);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000);
            
            if (diff < 60) return `${diff}s ago`;
            if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
            return `${Math.floor(diff / 3600)}h ago`;
        }
    </script>
</div>
