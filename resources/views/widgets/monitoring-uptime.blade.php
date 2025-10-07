@php
    $overallStatus = $data['overall_status'] ?? 'unknown';
    $averageUptime = $data['average_uptime_24h'] ?? 100;
    $totalMonitored = $data['total_monitored'] ?? 0;
    
    // Display name based on number of sites
    if ($totalMonitored > 1) {
        $displayName = $totalMonitored . ' nettsider';
    } else {
        // Single site - show hostname
        $websites = $userWidget->settings['websites'] ?? null;
        if ($websites && count($websites) > 0) {
            $displayName = $websites[0]['name'] ?? parse_url($websites[0]['url'], PHP_URL_HOST);
        } else {
            // Fallback to old format
            $monitoredUrl = $userWidget->settings['url'] ?? 'https://smartesider.no';
            $displayName = parse_url($monitoredUrl, PHP_URL_HOST) ?? $monitoredUrl;
        }
    }
    
    // Determine status styling
    if ($overallStatus === 'all_up') {
        $statusClass = 'status-all-up';
        $statusIcon = '✅';
        $statusText = 'All Systems Operational';
    } elseif ($overallStatus === 'partial_down') {
        $statusClass = 'status-partial-down';
        $statusIcon = '⚠️';
        $statusText = 'Some Systems Down';
    } elseif ($overallStatus === 'all_down') {
        $statusClass = 'status-all-down';
        $statusIcon = '🚨';
        $statusText = 'Critical - All Down!';
    } else {
        $statusClass = 'status-unknown';
        $statusIcon = '❓';
        $statusText = 'Status Unknown';
    }
@endphp

<style>
    @keyframes pulse-success {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.05); opacity: 0.8; }
    }
    
    @keyframes pulse-warning {
        0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); }
        50% { transform: scale(1.02); box-shadow: 0 0 0 8px rgba(245, 158, 11, 0); }
    }
    
    @keyframes pulse-danger {
        0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.9); }
        50% { transform: scale(1.03); box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
    }
    
    @keyframes radar-scan {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    @keyframes signal-wave {
        0%, 100% { transform: scale(0.8); opacity: 0; }
        50% { transform: scale(1.5); opacity: 0.5; }
    }
    
    @keyframes heartbeat {
        0%, 100% { transform: scale(1); }
        10% { transform: scale(1.1); }
        20% { transform: scale(1); }
        30% { transform: scale(1.1); }
        40% { transform: scale(1); }
    }
    
    @keyframes status-slide-in {
        from { transform: translateX(-20px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes response-bar {
        from { width: 0; }
        to { width: 100%; }
    }
    
    .status-all-up {
        background: linear-gradient(135deg, #10B981 0%, #34D399 50%, #6EE7B7 100%);
    }
    
    .status-partial-down {
        background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 50%, #FCD34D 100%);
        animation: pulse-warning 2s ease-in-out infinite;
    }
    
    .status-all-down {
        background: linear-gradient(135deg, #DC2626 0%, #EF4444 50%, #F87171 100%);
        animation: pulse-danger 1.5s ease-in-out infinite;
    }
    
    .status-unknown {
        background: linear-gradient(135deg, #6B7280 0%, #9CA3AF 50%, #D1D5DB 100%);
    }
    
    .radar-sweep {
        position: absolute;
        width: 100%;
        height: 100%;
        background: conic-gradient(from 0deg, transparent, rgba(255,255,255,0.2), transparent);
        animation: radar-scan 4s linear infinite;
        border-radius: 50%;
    }
    
    .signal-ring {
        position: absolute;
        width: 100%;
        height: 100%;
        border: 2px solid rgba(255,255,255,0.5);
        border-radius: 50%;
        animation: signal-wave 2s ease-out infinite;
    }
    
    .site-up {
        background: rgba(16, 185, 129, 0.2);
        border-left: 4px solid #10B981;
    }
    
    .site-down {
        background: rgba(239, 68, 68, 0.2);
        border-left: 4px solid #EF4444;
        animation: pulse-danger 2s ease-in-out infinite;
    }
    
    .site-item {
        animation: status-slide-in 0.4s ease-out forwards;
    }
    
    .heartbeat-icon {
        animation: heartbeat 2s ease-in-out infinite;
    }
    
    .response-time-bar {
        animation: response-bar 1s ease-out forwards;
    }
    
    .uptime-circle {
        stroke-dasharray: 283; /* 2 * π * 45 */
        stroke-dashoffset: 283;
        animation: fill-circle 2s ease-out forwards;
        transform-origin: 50% 50%;
        transform: rotate(-90deg);
    }
    
    @keyframes fill-circle {
        to { stroke-dashoffset: calc(283 - (283 * var(--uptime) / 100)); }
    }
</style>

<div class="h-full flex flex-col overflow-hidden shadow-sm sm:rounded-lg relative {{ $statusClass }}">
    <!-- Radar Effect (only when all up) -->
    @if($overallStatus === 'all_up')
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-32 h-32 opacity-10">
            <div class="radar-sweep"></div>
        </div>
    @endif
    
    <!-- Signal Rings (only when monitoring) -->
    @if($overallStatus !== 'unknown')
        @for($i = 0; $i < 3; $i++)
            <div class="absolute top-4 right-4 w-8 h-8 opacity-20" style="animation-delay: {{ $i * 0.7 }}s;">
                <div class="signal-ring"></div>
            </div>
        @endfor
    @endif
    
    <div class="p-2 relative z-0">
        <!-- Header -->
        <div class="flex items-center gap-2 mb-2">
            <span class="text-2xl heartbeat-icon">{{ $statusIcon }}</span>
            <div class="flex-1">
                <h3 class="text-sm font-bold text-white drop-shadow-lg">{{ $displayName }}</h3>
                <p class="text-xs text-white text-opacity-90 drop-shadow">{{ $statusText }}</p>
            </div>
        </div>
        
        <!-- Stats Row -->
        <div class="grid grid-cols-3 gap-1 mb-2">
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-2 text-center">
                <div class="text-xl font-bold text-white drop-shadow">{{ number_format($averageUptime, 1) }}%</div>
                <div class="text-xs text-white text-opacity-80">24h Uptime</div>
            </div>
            <div class="bg-white bg-opacity-30 backdrop-blur-sm rounded-lg p-2 text-center">
                <div class="text-xl font-bold text-emerald-100 drop-shadow">{{ $data['currently_up'] ?? 0 }}</div>
                <div class="text-xs text-white text-opacity-80">🟢 Up</div>
            </div>
            <div class="bg-white bg-opacity-30 backdrop-blur-sm rounded-lg p-2 text-center">
                <div class="text-xl font-bold text-red-100 drop-shadow">{{ $data['currently_down'] ?? 0 }}</div>
                <div class="text-xs text-white text-opacity-80">🔴 Down</div>
            </div>
        </div>
        
        <!-- Website Status List -->
        <div class="space-y-1 max-h-64 overflow-y-auto">
            @if(isset($data['websites']) && count($data['websites']) > 0)
                @foreach($data['websites'] as $index => $site)
                    <div class="backdrop-blur-sm rounded-lg p-2 {{ $site['is_up'] ? 'site-up' : 'site-down' }} site-item"
                         style="animation-delay: {{ $index * 0.1 }}s;">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm">{{ $site['is_up'] ? '🟢' : '🔴' }}</span>
                                    <span class="text-xs font-bold text-white drop-shadow truncate">
                                        {{ $site['name'] }}
                                    </span>
                                </div>
                                
                                <div class="flex items-center gap-2 mt-1">
                                    <!-- Response Time -->
                                    <div class="flex items-center gap-1">
                                        <span class="text-xs text-white text-opacity-70">⚡</span>
                                        <span class="text-xs text-white text-opacity-90 drop-shadow">
                                            {{ $site['response_time'] }}ms
                                        </span>
                                    </div>
                                    
                                    <!-- Uptime Percentage -->
                                    <div class="flex items-center gap-1">
                                        <span class="text-xs text-white text-opacity-70">📊</span>
                                        <span class="text-xs text-white text-opacity-90 drop-shadow">
                                            {{ number_format($site['uptime_24h'], 1) }}%
                                        </span>
                                    </div>
                                    
                                    @if($site['error'])
                                        <span class="text-xs text-red-200 drop-shadow truncate">
                                            ⚠️ {{ substr($site['error'], 0, 30) }}...
                                        </span>
                                    @endif
                                </div>
                                
                                <!-- Response Time Bar -->
                                @if($site['is_up'])
                                    <div class="mt-1 w-full bg-white bg-opacity-20 rounded-full h-1">
                                        <div class="response-time-bar h-1 rounded-full {{ $site['response_time'] < 500 ? 'bg-green-300' : ($site['response_time'] < 1000 ? 'bg-yellow-300' : 'bg-red-300') }}"
                                             style="width: {{ min(100, ($site['response_time'] / 2000) * 100) }}%; animation-delay: {{ $index * 0.1 }}s;">
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Uptime Circle Chart -->
                            <div class="ml-2 relative w-12 h-12 flex-shrink-0">
                                <svg class="w-full h-full" viewBox="0 0 100 100">
                                    <!-- Background circle -->
                                    <circle cx="50" cy="50" r="45" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="8"/>
                                    <!-- Uptime circle -->
                                    <circle class="uptime-circle" cx="50" cy="50" r="45" fill="none" 
                                            stroke="{{ $site['is_up'] ? '#10B981' : '#EF4444' }}" 
                                            stroke-width="8"
                                            style="--uptime: {{ $site['uptime_24h'] }}; animation-delay: {{ $index * 0.15 }}s;"/>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-xs font-bold text-white drop-shadow">
                                        {{ round($site['uptime_24h']) }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-8 text-white text-opacity-70">
                    <span class="text-4xl mb-2 block">🌐</span>
                    <p class="text-xs">No websites configured</p>
                </div>
            @endif
        </div>
        
        <!-- Footer -->
        <div class="mt-2 pt-2 border-t border-white border-opacity-20 text-center">
            <p class="text-xs text-white text-opacity-70">
                Sjekker hvert minutt • {{ $data['websites'][0]['checks_24h'] ?? 0 }} checks siste 24t
            </p>
        </div>
    </div>
</div>
