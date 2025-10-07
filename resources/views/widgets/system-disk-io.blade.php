@php
    $readMBs = $data['io']['read_mb_s'] ?? 0;
    $writeMBs = $data['io']['write_mb_s'] ?? 0;
    $totalMBs = $data['io']['total_mb_s'] ?? 0;
    $disk = $data['io']['disk'] ?? 'unknown';
    
    if ($totalMBs > 200) {
        $ioStatus = 'critical';
        $statusText = 'VELDIG HØY I/O';
    } elseif ($totalMBs > 100) {
        $ioStatus = 'warning';
        $statusText = 'HØY I/O';
    } elseif ($totalMBs > 50) {
        $ioStatus = 'moderate';
        $statusText = 'NORMAL I/O';
    } else {
        $ioStatus = 'low';
        $statusText = 'LAV I/O';
    }
@endphp

<div 
    x-data="widgetData('{{ $widget->key ?? 'system.disk-io' }}')" 
    x-init="init()"
    class="h-full flex flex-col overflow-hidden shadow-sm sm:rounded-lg relative io-{{ $ioStatus }}"
>
    <div class="p-2 relative z-0 flex-1 flex flex-col">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                <span class="text-2xl">💽</span>
                <div>
                    <div class="font-bold text-white drop-shadow">Disk I/O</div>
                    <div class="text-xs text-white text-opacity-70">{{ $disk }} - {{ $statusText }}</div>
                </div>
            </div>
        </div>

        <template x-if="error">
            <div class="bg-red-900 bg-opacity-50 backdrop-blur-sm border border-red-300 rounded-lg p-4 text-white text-xs" x-text="error"></div>
        </template>

        <template x-if="loading && !data">
            <div class="flex items-center justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
            </div>
        </template>

        <template x-if="!error && data">
            <div class="flex-1 flex flex-col">
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-2">
                        <div class="text-xs text-white text-opacity-70 mb-1">📖 Lesing</div>
                        <div class="text-xl font-bold text-white drop-shadow
                            @if($readMBs > 100) text-red-300
                            @elseif($readMBs > 50) text-yellow-300
                            @endif"
                        >{{ number_format($readMBs, 2) }}</div>
                        <div class="text-xs text-white text-opacity-60">MB/s</div>
                    </div>
                    
                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-2">
                        <div class="text-xs text-white text-opacity-70 mb-1">✍️ Skriving</div>
                        <div class="text-xl font-bold text-white drop-shadow
                            @if($writeMBs > 100) text-red-300
                            @elseif($writeMBs > 50) text-yellow-300
                            @endif"
                        >{{ number_format($writeMBs, 2) }}</div>
                        <div class="text-xs text-white text-opacity-60">MB/s</div>
                    </div>
                    
                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-2">
                        <div class="text-xs text-white text-opacity-70 mb-1">Totalt</div>
                        <div class="text-xl font-bold text-white drop-shadow
                            @if($totalMBs > 200) text-red-300
                            @elseif($totalMBs > 100) text-yellow-300
                            @endif"
                        >{{ number_format($totalMBs, 2) }}</div>
                        <div class="text-xs text-white text-opacity-60">MB/s</div>
                    </div>
                </div>

                <!-- Footer with Status Light and Timestamp -->
                <div class="flex items-center justify-between text-xs text-white text-opacity-70 pt-2 border-t border-white border-opacity-20 mt-2">
                    <!-- Status Light -->
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full transition-colors duration-300"
                              :class="{
                                  'bg-gray-400': statusLight === 'gray',
                                  'bg-yellow-400 animate-pulse': statusLight === 'yellow',
                                  'bg-green-400': statusLight === 'green',
                                  'bg-red-400': statusLight === 'red'
                              }"
                              :title="statusLight === 'yellow' ? 'Oppdaterer...' : statusLight === 'green' ? 'Oppdatert' : statusLight === 'red' ? 'Feil' : 'Inaktiv'"></span>
                        <span x-show="statusIcon" class="font-bold" x-text="statusIcon"></span>
                        <span x-show="statusLight === 'yellow'">Oppdaterer...</span>
                        <span x-show="statusLight === 'green'">Oppdatert</span>
                        <span x-show="statusLight === 'red'">Feil</span>
                    </div>
                    
                    <!-- Timestamp -->
                    <div>
                        <span x-text="lastUpdate || 'Starter...'"></span>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<style>
.io-low {
    background: linear-gradient(135deg, #10B981 0%, #34D399 100%);
}
.io-moderate {
    background: linear-gradient(135deg, #3B82F6 0%, #60A5FA 100%);
}
.io-warning {
    background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%);
}
.io-critical {
    background: linear-gradient(135deg, #EF4444 0%, #F87171 100%);
}
</style>
