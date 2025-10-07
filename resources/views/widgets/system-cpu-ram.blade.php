@php
    // Get CPU usage percentage
    $cpuPercent = $data['cpu_usage']['total'] ?? 0;
    $memoryPercent = $data['memory']['used_percent'] ?? 0;
    $loadAvg1min = $data['load_average']['1min'] ?? 0;
    
    // Estimate CPU cores (fallback to 2 if not available)
    $cpuCores = $data['cpu_cores'] ?? 2;
    $loadPercentage = ($loadAvg1min / $cpuCores) * 100;
    
    // Build detailed status messages
    $issues = [];
    if ($cpuPercent >= 85) {
        $issues[] = 'CPU: ' . round($cpuPercent, 0) . '% (kritisk)';
    } elseif ($cpuPercent >= 60) {
        $issues[] = 'CPU: ' . round($cpuPercent, 0) . '% (høyt)';
    }
    
    if ($memoryPercent >= 85) {
        $issues[] = 'Minne: ' . round($memoryPercent, 0) . '% (kritisk)';
    } elseif ($memoryPercent >= 60) {
        $issues[] = 'Minne: ' . round($memoryPercent, 0) . '% (høyt)';
    }
    
    // Determine overall performance status
    if ($cpuPercent >= 85 || $memoryPercent >= 85) {
        $perfStatus = 'critical'; // Red - Critical
        $statusText = 'KRITISK BELASTNING';
        $statusDetails = implode(', ', $issues);
    } elseif ($cpuPercent >= 60 || $memoryPercent >= 60) {
        $perfStatus = 'warning'; // Yellow - Warning
        $statusText = 'HØY BELASTNING';
        $statusDetails = implode(', ', $issues);
    } else {
        $perfStatus = 'optimal'; // Green - Optimal
        $statusText = 'OPTIMAL YTELSE';
        $statusDetails = 'CPU: ' . round($cpuPercent, 0) . '%, RAM: ' . round($memoryPercent, 0) . '%';
    }
@endphp

<style>
    @keyframes circuit-flow {
        0%, 100% { stroke-dashoffset: 100; }
        50% { stroke-dashoffset: 0; }
    }
    
    @keyframes chip-pulse {
        0%, 100% { transform: scale(1); opacity: 0.8; }
        50% { transform: scale(1.05); opacity: 1; }
    }
    
    @keyframes ram-bars {
        0%, 100% { transform: scaleY(0.6); }
        50% { transform: scaleY(1); }
    }
    
    @keyframes heat-wave {
        0%, 100% { transform: translateY(0px) scaleX(1); }
        33% { transform: translateY(-3px) scaleX(1.1); }
        66% { transform: translateY(3px) scaleX(0.9); }
    }
    
    @keyframes warning-blink {
        0%, 100% { opacity: 0.5; }
        50% { opacity: 1; }
    }
    
    .perf-optimal {
        background: linear-gradient(135deg, #10B981 0%, #34D399 50%, #6EE7B7 100%);
    }
    
    .perf-warning {
        background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 50%, #FCD34D 100%);
    }
    
    .perf-critical {
        background: linear-gradient(135deg, #DC2626 0%, #EF4444 50%, #F87171 100%);
    }
    
    .circuit-line {
        stroke-dasharray: 10 5;
        animation: circuit-flow 3s linear infinite;
    }
    
    .cpu-chip {
        animation: chip-pulse 2s ease-in-out infinite;
    }
    
    .ram-bar {
        animation: ram-bars 1.5s ease-in-out infinite;
    }
    
    .heat-wave {
        animation: heat-wave 2s ease-in-out infinite;
    }
    
    .perf-icon-optimal {
        animation: chip-pulse 3s ease-in-out infinite;
    }
    
    .perf-icon-warning {
        animation: warning-blink 1.5s ease-in-out infinite;
    }
    
    .perf-icon-critical {
        animation: shake-critical 0.5s ease-in-out infinite;
    }
</style>

<div 
    x-data="{
        ...widgetData('{{ $widget->key ?? 'system.cpu-ram' }}'),
        showDetails: {{ $perfStatus !== 'optimal' ? 'true' : 'false' }}
    }" 
    x-init="init(); $watch('data', () => {
        if (data) {
            const cpuPercent = data.cpu_usage?.total || 0;
            const memPercent = data.memory?.used_percent || 0;
            showDetails = (cpuPercent >= 60 || memPercent >= 60) ? true : showDetails;
        }
    })"
    class="h-full flex flex-col overflow-hidden shadow-sm sm:rounded-lg relative perf-{{ $perfStatus }}"
>
    <!-- Circuit Board Pattern -->
    <svg class="absolute inset-0 w-full h-full opacity-20" style="z-index: 1;">
        <!-- Horizontal lines -->
        <line x1="0" y1="20%" x2="100%" y2="20%" class="circuit-line" stroke="white" stroke-width="1"/>
        <line x1="0" y1="50%" x2="100%" y2="50%" class="circuit-line" stroke="white" stroke-width="1" style="animation-delay: 0.5s;"/>
        <line x1="0" y1="80%" x2="100%" y2="80%" class="circuit-line" stroke="white" stroke-width="1" style="animation-delay: 1s;"/>
        
        <!-- Vertical lines -->
        <line x1="30%" y1="0" x2="30%" y2="100%" class="circuit-line" stroke="white" stroke-width="1" style="animation-delay: 0.3s;"/>
        <line x1="70%" y1="0" x2="70%" y2="100%" class="circuit-line" stroke="white" stroke-width="1" style="animation-delay: 0.8s;"/>
        
        <!-- CPU Chip Icon -->
        <rect x="10%" y="10%" width="15" height="15" fill="rgba(255,255,255,0.3)" class="cpu-chip" rx="2"/>
        
        <!-- RAM Bars -->
        <rect x="80%" y="15%" width="3" height="10" fill="rgba(255,255,255,0.4)" class="ram-bar" style="animation-delay: 0s;"/>
        <rect x="85%" y="15%" width="3" height="10" fill="rgba(255,255,255,0.4)" class="ram-bar" style="animation-delay: 0.2s;"/>
        <rect x="90%" y="15%" width="3" height="10" fill="rgba(255,255,255,0.4)" class="ram-bar" style="animation-delay: 0.4s;"/>
    </svg>
    
    <!-- Heat Waves (only for warning/critical) -->
    @if($perfStatus !== 'optimal')
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden" style="z-index: 2; pointer-events: none;">
            @for($i = 0; $i < 3; $i++)
                <div class="heat-wave absolute bg-white opacity-10 rounded-full" 
                     style="width: 150%; height: 30px; left: -25%; top: {{ 20 + ($i * 30) }}%; animation-delay: {{ $i * 0.3 }}s;"></div>
            @endfor
        </div>
    @endif
    
    <div class="p-2 relative z-0">
        <div class="flex items-center justify-between mb-0.5">
            <div class="flex items-center gap-2">
                <span class="text-2xl perf-icon-{{ $perfStatus }}">
                    @if($perfStatus === 'optimal')
                        💚
                    @elseif($perfStatus === 'warning')
                        ⚠️
                    @else
                        🔥
                    @endif
                </span>
                <h3 class="text-base font-semibold text-white drop-shadow-lg">CPU & RAM</h3>
            </div>
            <span class="text-xs text-white text-opacity-90 drop-shadow" x-text="loading ? 'Laster...' : 'Oppdatert'"></span>
        </div>

        <template x-if="error">
            <div class="bg-red-900 bg-opacity-50 backdrop-blur-sm border border-red-300 rounded-lg p-4 text-white text-xs" x-text="error"></div>
        </template>

        <template x-if="!error && data">
            <div class="space-y-0.5">
                <!-- Status Badge -->
                <div class="flex items-center gap-2 mb-2">
                    <div class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-white bg-opacity-20 backdrop-blur-sm text-white">
                        @if($perfStatus === 'optimal')
                            💚 {{ $statusText }}
                        @elseif($perfStatus === 'warning')
                            ⚠️ {{ $statusText }}
                        @else
                            🔥 {{ $statusText }}
                        @endif
                    </div>
                    
                    <!-- Details Badge (always show summary) -->
                    <div class="inline-block px-2 py-1 rounded text-xs bg-white bg-opacity-10 backdrop-blur-sm text-white text-opacity-90">
                        {{ $statusDetails }}
                    </div>
                    
                    <!-- Vis mer button (only when optimal) -->
                    <button 
                        x-show="(data.cpu_usage?.total || 0) < 60 && (data.memory?.used_percent || 0) < 60"
                        @click="showDetails = !showDetails"
                        class="ml-auto px-2 py-1 rounded text-xs bg-white bg-opacity-20 hover:bg-opacity-30 text-white transition-all duration-200"
                    >
                        <span x-show="!showDetails">📊 Vis detaljer</span>
                        <span x-show="showDetails">▲ Skjul detaljer</span>
                    </button>
                </div>
                
                <!-- Detailed sections (hidden when optimal and showDetails=false) -->
                <div x-show="showDetails" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="space-y-0.5">
                <!-- CPU Usage -->
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-3">
                    <div class="text-xs text-white text-opacity-80 mb-0.5">CPU Bruk</div>
                    <div class="flex items-end justify-between mb-1">
                        <div class="text-3xl font-bold text-white drop-shadow" x-text="(data.cpu_usage?.total || 0).toFixed(1) + '%'"></div>
                        <div class="text-xs text-white text-opacity-70 text-right space-y-0.5">
                            <div>Bruker: <span class="font-semibold" x-text="(data.cpu_usage?.user || 0).toFixed(1) + '%'"></span></div>
                            <div>System: <span class="font-semibold" x-text="(data.cpu_usage?.system || 0).toFixed(1) + '%'"></span></div>
                            <div>Ledig: <span class="font-semibold" x-text="(data.cpu_usage?.idle || 0).toFixed(1) + '%'"></span></div>
                        </div>
                    </div>
                    <div class="w-full bg-black bg-opacity-30 rounded-full h-2">
                        <div 
                            class="h-2 rounded-full transition-all duration-500"
                            :class="{
                                'bg-green-300': (data.cpu_usage?.total || 0) < 60,
                                'bg-yellow-300': (data.cpu_usage?.total || 0) >= 60 && (data.cpu_usage?.total || 0) < 85,
                                'bg-red-300': (data.cpu_usage?.total || 0) >= 85
                            }"
                            :style="`width: ${data.cpu_usage?.total || 0}%`"
                        ></div>
                    </div>
                </div>
                
                <!-- Memory Usage -->
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-3">
                    <div class="text-xs text-white text-opacity-80 mb-0.5">Minnebruk</div>
                    <div class="flex items-end justify-between mb-1">
                        <div>
                            <span class="text-base font-bold text-white drop-shadow" x-text="data.memory?.formatted?.used || '0 MB'"></span>
                            <span class="text-xs text-white text-opacity-70">/ <span x-text="data.memory?.formatted?.total || '0 MB'"></span></span>
                        </div>
                        <div class="text-base font-semibold text-white drop-shadow" x-text="(data.memory?.used_percent || 0) + '%'"></div>
                    </div>
                    <div class="w-full bg-black bg-opacity-30 rounded-full h-1.5">
                        <div 
                            class="h-1.5 rounded-full transition-all duration-500"
                            :class="{
                                'bg-green-300': (data.memory?.used_percent || 0) < 60,
                                'bg-yellow-300': (data.memory?.used_percent || 0) >= 60 && (data.memory?.used_percent || 0) < 85,
                                'bg-red-300': (data.memory?.used_percent || 0) >= 85
                            }"
                            :style="`width: ${data.memory?.used_percent || 0}%`"
                        ></div>
                    </div>
                    <div class="text-xs text-white text-opacity-70 mt-1">
                        Tilgjengelig: <span x-text="data.memory?.formatted?.available || '0 MB'"></span>
                    </div>
                </div>

                <!-- Swap Usage (if exists) -->
                <div x-show="data.memory?.swap_total > 0" class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-2">
                    <div class="text-xs text-white text-opacity-80 mb-1">Swap-bruk</div>
                    <div class="flex items-center justify-between text-xs text-white text-opacity-90">
                        <span x-text="data.memory?.formatted?.swap_used || '0 MB'"></span>
                        <span class="text-white text-opacity-70">/ <span x-text="data.memory?.formatted?.swap_total || '0 MB'"></span></span>
                    </div>
                </div>
                
                <!-- Top CPU Processes -->
                <div x-show="data.top_processes?.by_cpu?.length > 0" class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-3">
                    <div class="text-xs text-white text-opacity-80 mb-2 font-semibold">🔥 Topp CPU-forbrukere</div>
                    <div class="space-y-1">
                        <template x-for="(proc, idx) in (data.top_processes?.by_cpu || []).slice(0, 3)" :key="idx">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex-1 truncate">
                                    <span class="font-mono text-white text-opacity-90" x-text="proc.user"></span>
                                    <span class="text-white text-opacity-70 ml-1" x-text="'(' + proc.command + ')'"></span>
                                </div>
                                <div class="ml-2 font-semibold" 
                                     :class="{
                                         'text-red-300': parseFloat(proc.cpu) >= 80,
                                         'text-yellow-300': parseFloat(proc.cpu) >= 50 && parseFloat(proc.cpu) < 80,
                                         'text-green-300': parseFloat(proc.cpu) < 50
                                     }"
                                     x-text="proc.cpu + '%'">
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                
                <!-- Top Memory Processes -->
                <div x-show="data.top_processes?.by_memory?.length > 0" class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-3">
                    <div class="text-xs text-white text-opacity-80 mb-2 font-semibold">💾 Topp RAM-forbrukere</div>
                    <div class="space-y-1">
                        <template x-for="(proc, idx) in (data.top_processes?.by_memory || []).slice(0, 3)" :key="idx">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex-1 truncate">
                                    <span class="font-mono text-white text-opacity-90" x-text="proc.user"></span>
                                    <span class="text-white text-opacity-70 ml-1" x-text="'(' + proc.command + ')'"></span>
                                </div>
                                <div class="ml-2 font-semibold" 
                                     :class="{
                                         'text-red-300': parseFloat(proc.mem) >= 80,
                                         'text-yellow-300': parseFloat(proc.mem) >= 50 && parseFloat(proc.mem) < 80,
                                         'text-green-300': parseFloat(proc.mem) < 50
                                     }"
                                     x-text="proc.mem + '%'">
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Load Average -->
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-3">
                    <div class="text-xs text-white text-opacity-80 mb-0.5">Load Average</div>
                    <div class="grid grid-cols-3 gap-1 text-center">
                        <div>
                            <div class="text-xs text-white text-opacity-70">1m</div>
                            <div class="font-semibold text-white drop-shadow" x-text="data.load_average?.['1min']?.toFixed(2) || '0.00'"></div>
                        </div>
                        <div>
                            <div class="text-xs text-white text-opacity-70">5m</div>
                            <div class="font-semibold text-white drop-shadow" x-text="data.load_average?.['5min']?.toFixed(2) || '0.00'"></div>
                        </div>
                        <div>
                            <div class="text-xs text-white text-opacity-70">15m</div>
                            <div class="font-semibold text-white drop-shadow" x-text="data.load_average?.['15min']?.toFixed(2) || '0.00'"></div>
                        </div>
                    </div>
                    <div class="text-xs text-white text-opacity-70 text-center mt-1">
                        Prosesser: <span x-text="data.load_average?.running_processes || 0"></span> kjørende / <span x-text="data.load_average?.total_processes || 0"></span> totalt
                    </div>
                </div>
                </div>
                <!-- End of detailed sections -->

                <!-- Disk I/O Section - ALWAYS VISIBLE -->
                <div x-show="data.disk_io" class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-3 mt-2">
                    <div class="flex items-center gap-1.5 mb-2">
                        <span class="text-base">💾</span>
                        <span class="text-xs text-white text-opacity-80 font-semibold">Disk I/O</span>
                        <span x-show="data.disk_io?.disk" class="text-xs text-white text-opacity-60" x-text="'(' + (data.disk_io?.disk || '') + ')'"></span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="text-center">
                            <div class="text-xs text-white text-opacity-70">📖 Lesing</div>
                            <div class="text-lg font-bold text-blue-300" x-text="(data.disk_io?.read_mb_s || 0).toFixed(2)"></div>
                            <div class="text-xs text-white text-opacity-60">MB/s</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xs text-white text-opacity-70">✍️ Skriving</div>
                            <div class="text-lg font-bold text-orange-300" x-text="(data.disk_io?.write_mb_s || 0).toFixed(2)"></div>
                            <div class="text-xs text-white text-opacity-60">MB/s</div>
                        </div>
                    </div>
                    <div class="mt-2 pt-2 border-t border-white border-opacity-20 text-center">
                        <div class="text-xs text-white text-opacity-70">Total I/O</div>
                        <div class="text-xl font-bold text-white" x-text="(data.disk_io?.total_mb_s || 0).toFixed(2)"></div>
                        <div class="text-xs text-white text-opacity-60">MB/s</div>
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
