@php
    // Determine system status based on load average
    // For a typical server with 2-4 CPU cores
    $loadAvg1min = $data['load_average']['1min'] ?? 0;
    $cpuCores = $data['cpu_cores'] ?? 2; // Default to 2 cores if not available
    
    // Calculate load percentage
    $loadPercentage = ($loadAvg1min / $cpuCores) * 100;
    
    if ($loadPercentage < 50) {
        $status = 'ok'; // Green - All OK
        $statusText = 'ALT OK';
        $statusDetails = 'Systemet kjører normalt';
    } elseif ($loadPercentage < 80) {
        $status = 'warning'; // Yellow - Something is off
        $statusText = 'MODERAT BELASTNING';
        $statusDetails = 'Load: ' . round($loadPercentage, 0) . '% (' . $loadAvg1min . ' av ' . $cpuCores . ' kjerner)';
    } else {
        $status = 'critical'; // Red - Full crisis
        $statusText = 'HØY BELASTNING!';
        $statusDetails = 'Load: ' . round($loadPercentage, 0) . '% (' . $loadAvg1min . ' av ' . $cpuCores . ' kjerner)';
    }
@endphp

<style>
    @keyframes pulse-green {
        0%, 100% { background-color: #10B981; }
        50% { background-color: #34D399; }
    }
    
    @keyframes pulse-yellow {
        0%, 100% { background-color: #F59E0B; }
        50% { background-color: #FBBF24; }
    }
    
    @keyframes pulse-red {
        0%, 100% { background-color: #EF4444; }
        50% { background-color: #F87171; }
    }
    
    @keyframes alert-flash {
        0%, 100% { opacity: 0.3; }
        50% { opacity: 1; }
    }
    
    @keyframes wave-ok {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
    
    @keyframes shake-warning {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-2px); }
        75% { transform: translateX(2px); }
    }
    
    @keyframes shake-critical {
        0%, 100% { transform: translateX(0) rotate(0deg); }
        10% { transform: translateX(-5px) rotate(-2deg); }
        20% { transform: translateX(5px) rotate(2deg); }
        30% { transform: translateX(-5px) rotate(-2deg); }
        40% { transform: translateX(5px) rotate(2deg); }
        50% { transform: translateX(-5px) rotate(-2deg); }
        60% { transform: translateX(5px) rotate(2deg); }
        70% { transform: translateX(-5px) rotate(-2deg); }
        80% { transform: translateX(5px) rotate(2deg); }
        90% { transform: translateX(-5px) rotate(-2deg); }
    }
    
    .status-ok {
        background: linear-gradient(135deg, #10B981 0%, #34D399 50%, #6EE7B7 100%);
    }
    
    .status-warning {
        background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 50%, #FCD34D 100%);
    }
    
    .status-critical {
        background: linear-gradient(135deg, #DC2626 0%, #EF4444 50%, #F87171 100%);
        animation: pulse-red 2s ease-in-out infinite;
    }
    
    .status-icon-ok {
        animation: wave-ok 3s ease-in-out infinite;
    }
    
    .status-icon-warning {
        animation: shake-warning 1s ease-in-out infinite;
    }
    
    .status-icon-critical {
        animation: shake-critical 0.5s ease-in-out infinite;
    }
    
    .alert-light {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        border-radius: 0.5rem;
        pointer-events: none;
    }
    
    .alert-light-critical {
        animation: alert-flash 1s ease-in-out infinite;
        background: radial-gradient(circle at center, rgba(239, 68, 68, 0.3) 0%, transparent 70%);
    }
    
    .alert-light-warning {
        animation: alert-flash 2s ease-in-out infinite;
        background: radial-gradient(circle at center, rgba(245, 158, 11, 0.2) 0%, transparent 70%);
    }
</style>

<div 
    x-data="widgetData('{{ $widget->key ?? 'system.uptime' }}')" 
    x-init="init()"
    class="h-full flex flex-col overflow-hidden shadow-sm sm:rounded-lg relative status-{{ $status }}"
>
    <!-- Alert Light Overlay -->
    @if($status === 'critical')
        <div class="alert-light alert-light-critical"></div>
    @elseif($status === 'warning')
        <div class="alert-light alert-light-warning"></div>
    @endif
    
    <div class="p-2 relative z-0">
        <div class="flex items-center justify-between mb-0.5">
            <div class="flex items-center gap-2">
                <span class="text-2xl status-icon-{{ $status }}">
                    @if($status === 'ok')
                        ✅
                    @elseif($status === 'warning')
                        ⚠️
                    @else
                        🚨
                    @endif
                </span>
                <h3 class="text-base font-semibold text-white drop-shadow-lg">Oppetid & Last</h3>
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
            <div class="flex items-center justify-center py-1">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
            </div>
        </template>

        <template x-if="!error && data">
            <div class="space-y-0.5">
                <!-- Status Badge -->
                <div class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-white bg-opacity-20 backdrop-blur-sm text-white mb-2">
                    @if($status === 'ok')
                        🟢 {{ $statusText }}
                    @elseif($status === 'warning')
                        🟡 {{ $statusText }}
                    @else
                        🔴 {{ $statusText }}
                    @endif
                </div>
                
                <!-- Details Badge (if not OK) -->
                @if($status !== 'ok')
                    <div class="inline-block ml-2 px-2 py-1 rounded text-xs bg-white bg-opacity-10 backdrop-blur-sm text-white text-opacity-90 mb-2">
                        {{ $statusDetails }}
                    </div>
                @endif
                
                <!-- Reboot Required Warning -->
                <div x-show="data.reboot_required?.required" class="bg-red-600 bg-opacity-90 backdrop-blur-sm border-2 border-red-300 rounded-lg p-3 mb-2">
                    <div class="flex items-start gap-2">
                        <span class="text-xl">🔄</span>
                        <div class="flex-1">
                            <div class="font-bold text-white text-sm mb-1">
                                ⚠️ Server trenger omstart!
                            </div>
                            <div class="text-xs text-white text-opacity-90 mb-1" x-text="data.reboot_required?.reason || 'Systemoppdateringer'"></div>
                            <template x-if="data.reboot_required?.details && data.reboot_required.details.length > 0">
                                <div class="text-xs text-white text-opacity-80 mt-1 space-y-0.5">
                                    <template x-for="detail in data.reboot_required.details" :key="detail">
                                        <div class="flex items-start gap-1">
                                            <span class="opacity-60">•</span>
                                            <span x-text="detail"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <div class="text-xs text-white text-opacity-70 mt-2" x-show="data.reboot_required?.running_kernel">
                                Kjørende kjerne: <span x-text="data.reboot_required?.running_kernel"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Uptime -->
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-3">
                    <div class="text-xs text-white text-opacity-80">Server oppetid</div>
                    <div class="text-base font-bold text-white drop-shadow" x-text="data.uptime?.formatted || 'Ukjent'"></div>
                    <div class="text-xs text-white text-opacity-70" x-show="data.boot_time">
                        Startet: <span x-text="formatDateTime(data.boot_time)"></span>
                    </div>
                </div>

                <!-- Load Average -->
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-3">
                    <div class="text-xs text-white text-opacity-80 mb-0.5">Systembelastning (load average)</div>
                    <div class="grid grid-cols-3 gap-1 text-center">
                        <div>
                            <div class="text-xs text-white text-opacity-70">1 min</div>
                            <div class="text-base font-semibold text-white drop-shadow" x-text="data.load_average?.['1min']?.toFixed(2) || '0.00'"></div>
                        </div>
                        <div>
                            <div class="text-xs text-white text-opacity-70">5 min</div>
                            <div class="text-base font-semibold text-white drop-shadow" x-text="data.load_average?.['5min']?.toFixed(2) || '0.00'"></div>
                        </div>
                        <div>
                            <div class="text-xs text-white text-opacity-70">15 min</div>
                            <div class="text-base font-semibold text-white drop-shadow" x-text="data.load_average?.['15min']?.toFixed(2) || '0.00'"></div>
                        </div>
                    </div>
                </div>

                <!-- Footer with Status Light and Timestamp -->
                <div class="flex items-center justify-between text-xs text-white text-opacity-70 pt-2 border-t border-white border-opacity-20">
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
