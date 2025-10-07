@php
    $summary = $data['summary'] ?? ['total' => 0, 'last_hour' => 0, 'by_level' => ['CRITICAL' => 0]];
    
    // Determine overall status
    if ($summary['by_level']['CRITICAL'] > 0) {
        $status = 'critical';
        $statusText = 'KRITISKE FEIL!';
        $bgClass = 'bg-gradient-to-br from-red-700 to-red-800';
    } elseif ($summary['last_hour'] > 5) {
        $status = 'warning';
        $statusText = 'MANGE FEIL';
        $bgClass = 'bg-gradient-to-br from-orange-600 to-orange-700';
    } elseif ($summary['last_hour'] > 0) {
        $status = 'attention';
        $statusText = 'NYE FEIL';
        $bgClass = 'bg-gradient-to-br from-yellow-600 to-yellow-700';
    } else {
        $status = 'ok';
        $statusText = 'INGEN FEIL';
        $bgClass = 'bg-gradient-to-br from-green-600 to-green-700';
    }
@endphp

<div 
    x-data="widgetData('{{ $widget->key ?? 'system.error-log' }}')" 
    x-init="init()"
    class="h-full flex flex-col overflow-hidden shadow-sm sm:rounded-lg {{ $bgClass }}"
>
    <div class="p-2 flex-1 flex flex-col overflow-hidden">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                <span class="text-2xl">
                    @if($status === 'ok')
                        ✅
                    @elseif($status === 'attention')
                        ⚠️
                    @else
                        🚨
                    @endif
                </span>
                <h3 class="text-base font-semibold text-white drop-shadow-lg">Error Monitor</h3>
            </div>
            <div class="flex items-center gap-1">
                <span x-show="loading" class="inline-block w-2 h-2 bg-white rounded-full animate-pulse" title="Laster..."></span>
                <span x-show="!loading && !error" class="inline-block w-2 h-2 bg-white rounded-full" title="Live"></span>
                <span x-show="error" class="inline-block w-2 h-2 bg-red-900 rounded-full" title="Feil"></span>
                <span class="text-xs text-white text-opacity-90 drop-shadow" x-text="lastUpdate || 'Starter...'"></span>
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
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Summary Stats -->
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-2 mb-2">
                    <div class="grid grid-cols-4 gap-1 text-center mb-2">
                        <div>
                            <div class="text-xs text-white text-opacity-70">Siste time</div>
                            <div class="text-xl font-bold text-white" x-text="data.summary?.last_hour || 0"></div>
                        </div>
                        <div>
                            <div class="text-xs text-white text-opacity-70">Siste 24t</div>
                            <div class="text-lg font-semibold text-white" x-text="data.summary?.last_24h || 0"></div>
                        </div>
                        <div>
                            <div class="text-xs text-white text-opacity-70">Kritiske</div>
                            <div class="text-lg font-semibold text-red-200" x-text="data.summary?.by_level?.CRITICAL || 0"></div>
                        </div>
                        <div>
                            <div class="text-xs text-white text-opacity-70">Totalt</div>
                            <div class="text-lg font-semibold text-white text-opacity-80" x-text="data.summary?.total || 0"></div>
                        </div>
                    </div>
                    
                    <!-- By Source -->
                    <div class="grid grid-cols-3 gap-1 text-center text-xs">
                        <div>
                            <div class="text-white text-opacity-60">Laravel</div>
                            <div class="text-sm font-semibold text-white" x-text="data.summary?.by_source?.Laravel || 0"></div>
                        </div>
                        <div>
                            <div class="text-white text-opacity-60">PHP</div>
                            <div class="text-sm font-semibold text-white" x-text="data.summary?.by_source?.PHP || 0"></div>
                        </div>
                        <div>
                            <div class="text-white text-opacity-60">Nginx</div>
                            <div class="text-sm font-semibold text-white" x-text="data.summary?.by_source?.Nginx || 0"></div>
                        </div>
                    </div>
                </div>

                <!-- Customer Recurring Errors Section -->
                <template x-if="data.customer_errors && Object.keys(data.customer_errors).length > 0">
                    <div class="mb-2">
                        <div class="text-xs font-semibold text-white text-opacity-90 mb-1 flex items-center gap-1">
                            <span>🏢</span>
                            <span>Kunders gjentagende feil</span>
                            <span class="text-white text-opacity-70" x-text="'(' + Object.keys(data.customer_errors).length + ' domener)'"></span>
                        </div>
                        
                        <div class="space-y-1 max-h-40 overflow-y-auto pr-1" style="scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.3) transparent;">
                            <template x-for="[domain, info] in Object.entries(data.customer_errors)" :key="domain">
                                <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-lg p-2">
                                    <div class="text-sm font-semibold text-white mb-1" x-text="domain"></div>
                                    <div class="text-xs text-white text-opacity-80 mb-1">
                                        <span x-text="info.total_errors"></span> feil totalt, 
                                        <span class="text-red-300 font-semibold" x-text="info.recurring_errors"></span> gjentagende
                                    </div>
                                    
                                    <template x-if="info.top_recurring && Object.keys(info.top_recurring).length > 0">
                                        <div class="space-y-0.5 mt-1">
                                            <template x-for="[sig, rec] in Object.entries(info.top_recurring).slice(0, 2)" :key="sig">
                                                <div class="bg-white bg-opacity-10 rounded p-1">
                                                    <div class="flex items-center gap-1 text-xs">
                                                        <span class="bg-red-500 text-white px-1 rounded font-mono" x-text="rec.count + 'x'"></span>
                                                        <span class="text-white text-opacity-60" x-text="rec.source"></span>
                                                        <span class="text-white text-opacity-90 truncate flex-1" x-text="rec.message.substring(0, 50) + '...'"></span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Error List -->
                <div class="flex-1 overflow-y-auto space-y-1 pr-1" style="scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.3) transparent;">
                    <template x-for="(err, index) in (data.errors || [])" :key="index">
                        <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-lg p-2 hover:bg-opacity-25 transition-all">
                            <div class="flex items-start gap-2">
                                <span x-show="err.level === 'CRITICAL' || err.level === 'EMERGENCY'">🔴</span>
                                <span x-show="err.level === 'ERROR'">🟠</span>
                                <span x-show="err.level === 'WARNING' || err.level === 'NOTICE'">🟡</span>
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <span class="text-xs font-semibold text-white px-1.5 py-0.5 rounded" 
                                              :class="{
                                                  'bg-red-500': err.level === 'CRITICAL' || err.level === 'EMERGENCY',
                                                  'bg-orange-500': err.level === 'ERROR',
                                                  'bg-yellow-500': err.level === 'WARNING'
                                              }"
                                              x-text="err.level"></span>
                                        <span class="text-xs text-white text-opacity-60" x-text="err.source"></span>
                                        <span class="text-xs text-white text-opacity-50 ml-auto" x-text="formatTime(err.timestamp)"></span>
                                    </div>
                                    
                                    <div class="text-xs text-white text-opacity-90 break-words" x-text="err.message"></div>
                                    
                                    <template x-if="err.details && err.details.length > 0">
                                        <div class="mt-1 text-xs text-white text-opacity-60 font-mono" x-text="err.details[0]"></div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                    
                    <template x-if="!data.errors || data.errors.length === 0">
                        <div class="text-center py-8 text-white text-opacity-90 text-sm">
                            <div class="text-4xl mb-2">🎉</div>
                            <div class="font-semibold">Ingen feil funnet!</div>
                            <div class="text-xs text-white text-opacity-70 mt-1">Systemet kjører perfekt</div>
                        </div>
                    </template>
                </div>

                <!-- Footer with Status Light and Timestamp -->
                <div class="flex items-center justify-between text-xs text-white text-opacity-70 pt-2 mt-2 border-t border-white border-opacity-20">
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

<script>
    // Helper function for Alpine.js
    window.formatTime = function(timestamp) {
        if (!timestamp) return '';
        const date = new Date(timestamp.replace(' ', 'T'));
        return date.toLocaleTimeString('nb-NO', { hour: '2-digit', minute: '2-digit' });
    };
</script>
