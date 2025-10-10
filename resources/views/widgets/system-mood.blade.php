@php
    $moodColor = $data['color'] ?? 'excellent';
@endphp

<style>
    .mood-excellent {
        background: linear-gradient(135deg, #10B981 0%, #34D399 50%, #6EE7B7 100%);
    }
    
    .mood-warning {
        background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 50%, #FCD34D 100%);
    }
    
    .mood-critical {
        background: linear-gradient(135deg, #DC2626 0%, #EF4444 50%, #F87171 100%);
    }

    @keyframes mood-pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
    
    .mood-emoji {
        animation: mood-pulse 2s ease-in-out infinite;
    }

    .sparkline { display: grid; grid-auto-flow: column; gap: 2px; align-items: end; height: 28px; }
    .sparkline > div { width: 6px; background: rgba(255,255,255,0.7); border-radius: 2px; }
    .badge { font-size: 10px; padding: 2px 6px; border-radius: 9999px; background: rgba(255,255,255,0.2); backdrop-filter: blur(2px); }
</style>

<div 
    x-data="widgetData('system.mood')"
    class="widget-card mood-{{ $moodColor }} rounded-lg shadow-lg overflow-hidden border-2 border-white/30 h-full flex flex-col"
>
    <!-- Header -->
    <div class="p-3 border-b border-white/20 bg-black/20">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-900 drop-shadow-[0_2px_4px_rgba(255,255,255,0.9)]">
                Server Mood Indicator™
            </h3>
            <div class="flex items-center gap-2">
                <!-- Streak badges -->
                <div class="badge" x-show="(data?.streak_chill_seconds||0) > 0">
                    Chill streak: <span x-text="Math.floor((data?.streak_chill_seconds||0)/3600)"></span>h
                </div>
                <div class="badge" x-show="(data?.streak_ok_seconds||0) > 0">
                    Stable: <span x-text="Math.floor((data?.streak_ok_seconds||0)/3600)"></span>h
                </div>
                <div class="text-xs text-gray-800/80 drop-shadow-md" x-text="formatTimestamp(lastUpdate)"></div>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="p-6 text-center flex-1 flex items-center justify-center">
        <svg class="animate-spin h-8 w-8 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

    <!-- Error State -->
    <div x-show="error && !loading" class="p-6 text-center bg-black/20 flex-1 flex items-center justify-center">
        <p class="text-xs text-white/80">⚠️ Kan ikke lese humør</p>
    </div>

    <!-- Content -->
    <div x-show="!loading && !error" class="p-6 flex-1 flex flex-col items-center justify-center text-center">
        <!-- Big Emoji + Pet -->
        <div class="flex items-center gap-3 mb-4">
            <div class="text-7xl mood-emoji" x-text="data?.emoji || '😌'"></div>
            <div class="text-3xl" x-show="data?.pet">
                <span x-text="data?.pet?.emoji"></span>
                <span class="text-xs align-top">(<span x-text="data?.pet?.happiness"></span>%)</span>
            </div>
        </div>
        
        <!-- Mood Text -->
        <div class="text-2xl font-bold mb-4 text-gray-900 drop-shadow-[0_2px_4px_rgba(255,255,255,0.9)]" 
             x-text="data?.mood || 'Henter humør...'">
        </div>
        
        <!-- Stats -->
        <div class="grid grid-cols-2 gap-4 w-full mt-4">
            <div class="bg-black/20 rounded-lg p-3 border border-white/20">
                <div class="text-xs text-gray-800/80 drop-shadow-md mb-1">CPU</div>
                <div class="text-xl font-bold text-gray-900 drop-shadow-[0_2px_4px_rgba(255,255,255,0.9)]">
                    <span x-text="(data?.cpu_percent || 0).toFixed(1)"></span>%
                </div>
            </div>
            <div class="bg-black/20 rounded-lg p-3 border border-white/20">
                <div class="text-xs text-gray-800/80 drop-shadow-md mb-1">RAM</div>
                <div class="text-xl font-bold text-gray-900 drop-shadow-[0_2px_4px_rgba(255,255,255,0.9)]">
                    <span x-text="(data?.ram_percent || 0).toFixed(1)"></span>%
                </div>
            </div>
        </div>

        <!-- Coffee meter -->
        <div class="w-full mt-3 bg-black/20 rounded-lg p-3 border border-white/20">
            <div class="flex items-center justify-between">
                <div class="text-xs text-gray-800/80">☕ Kaffemeter</div>
                <div class="text-xs text-gray-800/80" x-text="data?.coffee?.text"></div>
            </div>
            <div class="flex gap-1 mt-2">
                <template x-for="i in 3">
                    <div class="w-6 h-6 rounded bg-white/20 flex items-center justify-center" 
                         :class="(data?.coffee?.level||0) >= i ? 'bg-white/40' : 'bg-white/10'">
                        ☕
                    </div>
                </template>
            </div>
        </div>

        <!-- 24h Sparkline -->
        <div class="w-full mt-3">
            <div class="text-xs text-gray-800/80 mb-1">Siste 24h (lavere er bedre)</div>
            <div class="sparkline">
                <template x-for="v in (data?.trend || [])">
                    <div :style="`height: ${Math.max(4, v/100*28)}px; background: ${v<40?'#10B981':(v<70?'#F59E0B':'#EF4444')}`"></div>
                </template>
            </div>
        </div>
    </div>
</div>
