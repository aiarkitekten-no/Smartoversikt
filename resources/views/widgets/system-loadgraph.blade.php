@php
    $avgCpu = $data['avg_cpu'] ?? 0;
    $avgMemory = $data['avg_memory'] ?? 0;
    $avgLoad = ($avgCpu + $avgMemory) / 2;
    
    if ($avgLoad < 40) {
        $graphStatus = 'excellent';
    } elseif ($avgLoad < 70) {
        $graphStatus = 'warning';
    } else {
        $graphStatus = 'critical';
    }
@endphp

<style>
    .graph-excellent {
        background: linear-gradient(135deg, #10B981 0%, #34D399 50%, #6EE7B7 100%);
    }
    
    .graph-warning {
        background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 50%, #FCD34D 100%);
    }
    
    .graph-critical {
        background: linear-gradient(135deg, #DC2626 0%, #EF4444 50%, #F87171 100%);
    }

    @keyframes bar-grow {
        from { transform: scaleY(0); }
        to { transform: scaleY(1); }
    }
    
    .graph-bar {
        animation: bar-grow 0.5s ease-out;
        transform-origin: bottom;
    }
</style>

<div 
    x-data="widgetData('system.loadgraph')"
    class="widget-card graph-{{ $graphStatus }} rounded-lg shadow-lg overflow-hidden border-2 border-white/30 h-full flex flex-col"
>
    <!-- Header -->
    <div class="p-3 border-b border-white/20 bg-black/20">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-900 drop-shadow-[0_2px_4px_rgba(255,255,255,0.9)]">
                📊 Average Load (Last 7 Days)
            </h3>
            <div class="text-xs text-gray-800/80 drop-shadow-md" x-text="formatTimestamp(lastUpdate)"></div>
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
        <p class="text-xs text-white/80">⚠️ Ingen historikk</p>
    </div>

    <!-- Content -->
    <div x-show="!loading && !error" class="p-4 flex-1 flex flex-col">
        <!-- Stats Summary -->
        <div class="grid grid-cols-2 gap-2 mb-4">
            <div class="bg-black/20 rounded p-2 border border-white/20">
                <div class="text-[10px] text-gray-800/80 drop-shadow-md">Snitt CPU</div>
                <div class="text-lg font-bold text-gray-900 drop-shadow-[0_2px_4px_rgba(255,255,255,0.9)]">
                    <span x-text="(data?.avg_cpu || 0).toFixed(1)"></span>%
                </div>
            </div>
            <div class="bg-black/20 rounded p-2 border border-white/20">
                <div class="text-[10px] text-gray-800/80 drop-shadow-md">Snitt RAM</div>
                <div class="text-lg font-bold text-gray-900 drop-shadow-[0_2px_4px_rgba(255,255,255,0.9)]">
                    <span x-text="(data?.avg_memory || 0).toFixed(1)"></span>%
                </div>
            </div>
        </div>

        <!-- Graph -->
        <div x-show="data?.history?.length > 0" class="flex-1 flex items-end justify-around gap-1">
            <template x-for="day in data?.history || []" :key="day.day">
                <div class="flex-1 flex flex-col items-center">
                    <!-- Bars -->
                    <div class="w-full flex gap-0.5 items-end mb-1" style="height: 80px;">
                        <!-- CPU Bar -->
                        <div class="flex-1 bg-blue-500/70 rounded-t graph-bar border border-white/20"
                             :style="`height: ${Math.max(day.avg_cpu, 3)}%`"
                             :title="`CPU: ${day.avg_cpu}%`">
                        </div>
                        <!-- RAM Bar -->
                        <div class="flex-1 bg-purple-500/70 rounded-t graph-bar border border-white/20"
                             :style="`height: ${Math.max(day.avg_memory, 3)}%`"
                             :title="`RAM: ${day.avg_memory}%`">
                        </div>
                    </div>
                    <!-- Date Label -->
                    <div class="text-[9px] text-gray-800/80 drop-shadow-md whitespace-nowrap" 
                         x-text="day.day.substring(5)">
                    </div>
                </div>
            </template>
        </div>

        <!-- No Data Message -->
        <div x-show="!data?.history || data?.history?.length === 0" 
             class="flex-1 flex items-center justify-center">
            <p class="text-sm text-gray-800/60 drop-shadow-md">📊 Samler inn data...</p>
        </div>

        <!-- Legend -->
        <div class="flex justify-center gap-3 mt-3 pt-3 border-t border-white/20">
            <div class="flex items-center gap-1">
                <div class="w-3 h-3 bg-blue-500/70 rounded border border-white/20"></div>
                <span class="text-[10px] text-gray-800/80 drop-shadow-md">CPU</span>
            </div>
            <div class="flex items-center gap-1">
                <div class="w-3 h-3 bg-purple-500/70 rounded border border-white/20"></div>
                <span class="text-[10px] text-gray-800/80 drop-shadow-md">RAM</span>
            </div>
        </div>
    </div>
</div>
