@php
    $totalPartitions = count($data['partitions'] ?? []);
    $criticalCount = 0;
    $warningCount = 0;
    
    foreach ($data['partitions'] ?? [] as $partition) {
        if ($partition['status'] === 'critical') $criticalCount++;
        elseif ($partition['status'] === 'warning') $warningCount++;
    }
    
    if ($criticalCount > 0) {
        $diskStatus = 'critical';
        $statusText = 'KRITISK LAV PLASS';
    } elseif ($warningCount > 0) {
        $diskStatus = 'warning';
        $statusText = 'LAV DISKPLASS';
    } else {
        $diskStatus = 'normal';
        $statusText = 'TILSTREKKELIG PLASS';
    }
@endphp

<div 
    x-data="widgetData('{{ $widget->key ?? 'system.disk-usage' }}')" 
    x-init="init()"
    class="h-full flex flex-col overflow-hidden shadow-sm sm:rounded-lg relative disk-{{ $diskStatus }}"
>
    <div class="p-2 relative z-0 flex-1 flex flex-col">
        <div class="flex items-center justify-between mb-0.5">
            <div class="flex items-center gap-2">
                <span class="text-2xl">💿</span>
                <div>
                    <div class="font-bold text-white drop-shadow">Diskplass</div>
                    <div class="text-xs text-white text-opacity-70">{{ $totalPartitions }} partisjoner</div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-xs font-semibold text-white text-opacity-90">{{ $statusText }}</div>
            </div>
        </div>

        <template x-if="error">
            <div class="bg-red-900 bg-opacity-50 backdrop-blur-sm border border-red-300 rounded-lg p-4 text-white text-xs mt-2" x-text="error"></div>
        </template>

        <template x-if="loading && !data">
            <div class="flex items-center justify-center py-8 flex-1">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
            </div>
        </template>

        <template x-if="!error && data">
            <div class="flex-1 flex flex-col">
                @if(empty($data['partitions']))
                    <div class="text-center py-4 text-white text-opacity-70 text-sm">
                        Ingen partisjoner funnet
                    </div>
                @else
                    <div class="space-y-1.5 mt-2 flex-1 overflow-y-auto">
                        @foreach($data['partitions'] as $partition)
                            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-2">
                                <div class="flex items-center justify-between mb-0.5">
                                    <span class="text-white text-opacity-90 font-medium text-sm">{{ $partition['mount'] }}</span>
                                    <span class="font-bold text-sm
                                        @if($partition['status'] === 'critical') text-red-300
                                        @elseif($partition['status'] === 'warning') text-yellow-300
                                        @else text-green-300
                                        @endif"
                                    >{{ $partition['percent'] }}%</span>
                                </div>
                                <div class="flex items-center justify-between text-xs text-white text-opacity-60 mb-1">
                                    <span>{{ $partition['used'] }} / {{ $partition['size'] }}</span>
                                    <span>{{ $partition['available'] }} ledig</span>
                                </div>
                                <div class="w-full bg-black bg-opacity-30 rounded-full h-1.5">
                                    <div 
                                        class="h-1.5 rounded-full transition-all duration-500
                                            @if($partition['status'] === 'critical') bg-red-300
                                            @elseif($partition['status'] === 'warning') bg-yellow-300
                                            @else bg-green-300
                                            @endif"
                                        style="width: {{ $partition['percent'] }}%"
                                    ></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Network Traffic Section --}}
                @if(!empty($data['network']))
                    <div class="mt-2 pt-2 border-t border-white border-opacity-20">
                        <div class="text-xs font-semibold text-white text-opacity-90 mb-1">🌐 Nettverkstrafikk</div>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="bg-white bg-opacity-10 rounded p-1">
                                <div class="text-xs text-white text-opacity-60">↓ Inn</div>
                                <div class="text-sm font-bold text-white">{{ number_format($data['network']['rx_mbps'] ?? 0, 1) }}</div>
                                <div class="text-xs text-white text-opacity-50">Mbps</div>
                            </div>
                            <div class="bg-white bg-opacity-10 rounded p-1">
                                <div class="text-xs text-white text-opacity-60">↑ Ut</div>
                                <div class="text-sm font-bold text-white">{{ number_format($data['network']['tx_mbps'] ?? 0, 1) }}</div>
                                <div class="text-xs text-white text-opacity-50">Mbps</div>
                            </div>
                            <div class="bg-white bg-opacity-10 rounded p-1">
                                <div class="text-xs text-white text-opacity-60">Totalt</div>
                                <div class="text-sm font-bold text-white">{{ number_format($data['network']['total_mbps'] ?? 0, 1) }}</div>
                                <div class="text-xs text-white text-opacity-50">Mbps</div>
                            </div>
                        </div>
                    </div>
                @endif

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
.disk-normal {
    background: linear-gradient(135deg, #10B981 0%, #34D399 100%);
}
.disk-warning {
    background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%);
}
.disk-critical {
    background: linear-gradient(135deg, #EF4444 0%, #F87171 100%);
}
</style>
