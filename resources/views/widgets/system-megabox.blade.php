@php
    // Determine overall server status
    $cpuLoad = $data['cpu']['loadavg']['1min_percent'] ?? 0;
    $ramUsed = $data['memory']['used_percent'] ?? 0;
    $avgLoad = ($cpuLoad + $ramUsed) / 2;
    
    if ($avgLoad < 40) {
        $serverStatus = 'excellent'; // Green - All good!
    } elseif ($avgLoad < 70) {
        $serverStatus = 'warning'; // Yellow - Getting busy
    } else {
        $serverStatus = 'critical'; // Red - High load!
    }
@endphp

<style>
    .megabox-excellent {
        background: linear-gradient(135deg, #10B981 0%, #34D399 50%, #6EE7B7 100%);
    }
    
    .megabox-warning {
        background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 50%, #FCD34D 100%);
    }
    
    .megabox-critical {
        background: linear-gradient(135deg, #DC2626 0%, #EF4444 50%, #F87171 100%);
    }

    /* Fun animations for progress bars */
    @keyframes shimmer {
        0% { background-position: -200% center; }
        100% { background-position: 200% center; }
    }
    
    .animate-shimmer {
        animation: shimmer 3s ease-in-out infinite;
    }
    
    @keyframes pulse-slow {
        0%, 100% { opacity: 0.6; }
        50% { opacity: 1; }
    }
    
    .animate-pulse-slow {
        animation: pulse-slow 3s ease-in-out infinite;
    }
</style>

<div 
    x-data="{
        // Ensure local reactive `data` exists even before widgetData() returns
        data: null,
        loading: false,
        error: null,
        isFresh: false,
        lastUpdate: null,
        ...widgetData('system.megabox'),
        getOverallStatus() {
            const cpu = this.data?.cpu?.loadavg?.['1min_percent'] || 0;
            const ram = this.data?.memory?.used_percent || 0;
            const avg = (cpu + ram) / 2;
            if (avg < 40) return 'green';
            if (avg < 70) return 'yellow';
            return 'red';
        },
        getMemoryBarClass(percent) {
            if (percent < 60) return 'bg-green-500';
            if (percent < 80) return 'bg-yellow-500';
            return 'bg-red-500';
        },
        getCoreBarClass(usage) {
            if (usage < 30) return 'bg-green-500';
            if (usage < 60) return 'bg-yellow-500';
            return 'bg-red-500';
        }
    }"
    x-init="init()"
    class="widget-card rounded-xl shadow-lg p-6 border border-white/10 transition-all duration-300 megabox-{{ $serverStatus }}"
>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-white drop-shadow">MegaBox</h3>
                <p class="text-xs text-white/70">Server Performance Monitor</p>
            </div>
        </div>
        
        <!-- Last Update -->
        <div class="text-xs text-white/60" x-show="this.data?.timestamp">
            <span x-text="formatTimestamp(this.data?.timestamp)"></span>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-white"></div>
    </div>

    <!-- Error State -->
    <div x-show="error && !loading" class="bg-black/20 border border-white/30 rounded-lg p-4">
        <p class="text-white text-sm" x-text="error"></p>
    </div>

    <!-- Content -->
    <div x-show="!loading && !error" class="space-y-6">

        <!-- CPU Section -->
        <div x-show="data?.cpu" class="bg-black/20 rounded-lg p-4 border border-white/10">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                </svg>
                <h4 class="text-sm font-semibold text-white">CPU Performance</h4>
            </div>
            
            <div class="text-xs text-white/70 mb-3" x-text="data?.cpu?.model"></div>
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                </svg>
                <h4 class="text-base font-bold text-gray-900 drop-shadow-[0_2px_4px_rgba(255,255,255,0.9)]">CPU Performance</h4>
            </div>
            
            <div class="text-sm font-semibold text-gray-800 drop-shadow-[0_1px_2px_rgba(255,255,255,0.7)] mb-3" x-text="data?.cpu?.model"></div>
            
            <!-- Load Average -->
            <div class="grid grid-cols-3 gap-3 mb-4">
                <div class="bg-black/20 rounded-lg p-4 border border-white/20 transition-all hover:bg-black/30">
                    <div class="text-xs text-white/90 font-semibold mb-1 drop-shadow-md">Load 1 min</div>
                    <div class="text-3xl font-bold mb-1 text-gray-900 drop-shadow-[0_2px_4px_rgba(255,255,255,0.9)]" 
                         x-text="(data?.cpu?.loadavg?.['1min_percent'] || 0).toFixed(1) + '%'"></div>
                    <div class="text-xs text-white/80 drop-shadow-md" x-text="(data?.cpu?.loadavg?.['1min'] || 0).toFixed(2) + ' / ' + (data?.cpu?.cores || 12)"></div>
                    <div class="text-xs font-bold mt-2 text-gray-900 drop-shadow-[0_1px_2px_rgba(255,255,255,0.8)]">
                        <span x-show="data?.cpu?.loadavg?.['1min_percent'] < 30">💚 Excellent</span>
                        <span x-show="data?.cpu?.loadavg?.['1min_percent'] >= 30 && data?.cpu?.loadavg?.['1min_percent'] < 60">⚡ Good</span>
                        <span x-show="data?.cpu?.loadavg?.['1min_percent'] >= 60">🔥 Busy</span>
                    </div>
                </div>
                <div class="bg-black/20 rounded-lg p-4 border border-white/20 transition-all hover:bg-black/30">
                    <div class="text-xs text-white/90 font-semibold mb-1 drop-shadow-md">Load 5 min</div>
                    <div class="text-3xl font-bold mb-1 text-gray-900 drop-shadow-[0_2px_4px_rgba(255,255,255,0.9)]" 
                         x-text="(data?.cpu?.loadavg?.['5min_percent'] || 0).toFixed(1) + '%'"></div>
                    <div class="text-xs text-white/80 drop-shadow-md" x-text="(data?.cpu?.loadavg?.['5min'] || 0).toFixed(2) + ' / ' + (data?.cpu?.cores || 12)"></div>
                    <div class="text-xs font-bold mt-2 text-gray-900 drop-shadow-[0_1px_2px_rgba(255,255,255,0.8)]">
                        <span x-show="data?.cpu?.loadavg?.['5min_percent'] < 30">💚 Excellent</span>
                        <span x-show="data?.cpu?.loadavg?.['5min_percent'] >= 30 && data?.cpu?.loadavg?.['5min_percent'] < 60">⚡ Good</span>
                        <span x-show="data?.cpu?.loadavg?.['5min_percent'] >= 60">🔥 Busy</span>
                    </div>
                </div>
                <div class="bg-black/20 rounded-lg p-4 border border-white/20 transition-all hover:bg-black/30">
                    <div class="text-xs text-white/90 font-semibold mb-1 drop-shadow-md">Load 15 min</div>
                    <div class="text-3xl font-bold mb-1 text-gray-900 drop-shadow-[0_2px_4px_rgba(255,255,255,0.9)]" 
                         x-text="(data?.cpu?.loadavg?.['15min_percent'] || 0).toFixed(1) + '%'"></div>
                    <div class="text-xs text-white/80 drop-shadow-md" x-text="(data?.cpu?.loadavg?.['15min'] || 0).toFixed(2) + ' / ' + (data?.cpu?.cores || 12)"></div>
                    <div class="text-xs font-bold mt-2 text-gray-900 drop-shadow-[0_1px_2px_rgba(255,255,255,0.8)]">
                        <span x-show="data?.cpu?.loadavg?.['15min_percent'] < 30">💚 Excellent</span>
                        <span x-show="data?.cpu?.loadavg?.['15min_percent'] >= 30 && data?.cpu?.loadavg?.['15min_percent'] < 60">⚡ Good</span>
                        <span x-show="data?.cpu?.loadavg?.['15min_percent'] >= 60">🔥 Busy</span>
                    </div>
                </div>
            </div>

            <!-- Per-Core Usage -->
            <div x-show="data?.cpu?.per_core?.length > 0" class="space-y-2">
                <div class="flex justify-between items-center mb-3">
                    <div class="text-sm font-bold text-gray-900 drop-shadow-[0_1px_2px_rgba(255,255,255,0.8)]">
                        Per-Core Usage (<span x-text="data?.cpu?.cores || 0"></span> cores)
                    </div>
                    <div class="text-sm font-bold text-gray-900 drop-shadow-[0_2px_4px_rgba(255,255,255,0.9)]">
                        💪 Server Power: <span x-text="Math.round(100 - ((data?.cpu?.loadavg?.['1min_percent'] || 0)))"></span>% Available
                    </div>
                </div>
                <div class="grid grid-cols-6 gap-2">
                    <template x-for="core in data?.cpu?.per_core || []" :key="core.core">
                        <div class="bg-black/30 rounded-lg p-3 text-center hover:scale-105 transition-transform border border-white/20">
                            <div class="text-xs font-bold text-white mb-2" x-text="'C' + core.core"></div>
                            
                            <!-- INVERTED: Show AVAILABLE (green) with small used portion (gray) -->
                            <div class="h-6 bg-gray-400/30 rounded-full overflow-hidden mb-2 shadow-inner border border-white/20 relative">
                                <!-- Available portion (green) - fills from left -->
                                <div class="h-full transition-all duration-500 shadow-lg relative bg-gradient-to-r from-green-400 to-green-300" 
                                     :style="`width: ${100 - core.usage}%`">
                                    <!-- Shimmer effect for high availability (bragging!) -->
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent bg-[length:200%_100%] animate-shimmer" 
                                         x-show="core.usage < 20"></div>
                                </div>
                                <!-- Percentage text overlay -->
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-[10px] font-bold text-gray-900 drop-shadow-[0_1px_2px_rgba(255,255,255,0.8)]" 
                                          x-text="(100 - core.usage).toFixed(0) + '%'"></span>
                                </div>
                            </div>
                            
                            <div class="text-[10px] font-semibold text-white/90">
                                <span x-show="core.usage < 30">� Idle</span>
                                <span x-show="core.usage >= 30 && core.usage < 60">💼 Active</span>
                                <span x-show="core.usage >= 60">🔥 Busy</span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

       <!-- Memory Section -->
       <div x-show="data && data.memory" class="rounded-lg p-4 border transition-all"
           :class="(data?.memory?.used_percent ?? 0) < 60 ? 'bg-green-500/20 border-green-500/50' : (data?.memory?.used_percent ?? 0) < 80 ? 'bg-yellow-500/20 border-yellow-500/50' : 'bg-red-500/20 border-red-500/50'">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <h4 class="text-base font-bold text-gray-900 drop-shadow-[0_2px_4px_rgba(255,255,255,0.9)]">Memory (RAM)</h4>
            </div>

            <!-- Memory Bar -->
            <div class="mb-4">
                <div class="flex justify-between text-sm mb-3">
                    <span class="text-gray-900 font-bold drop-shadow-[0_1px_2px_rgba(255,255,255,0.8)]">
                        💚 <span x-text="(this.data?.memory?.available_gb || 0).toFixed(1) + ' GB'"></span> Available
                    </span>
                    <span class="text-gray-800/90 font-semibold drop-shadow-[0_1px_2px_rgba(255,255,255,0.6)]">
                        Total: <span x-text="(this.data?.memory?.total_gb || 0) + ' GB'"></span>
                    </span>
                </div>
                
                <!-- INVERTED: Show AVAILABLE (green) with small used portion (gray) -->
                <div class="h-10 bg-gray-400/30 rounded-full overflow-hidden relative shadow-inner border-2 border-white/30">
                    <!-- Available portion (green) - fills from left -->
                    <div class="h-full transition-all duration-700 shadow-xl relative bg-gradient-to-r from-green-400 via-green-300 to-emerald-300" 
                         :style="{ width: ((100 - (this.data?.memory?.used_percent || 0)) + '%') }">
                        <!-- Animated stripes for visual interest -->
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent bg-[length:200%_100%] animate-shimmer"></div>
                        <!-- Pulse effect for very high availability -->
                        <div class="absolute inset-0 animate-pulse-slow" 
                             x-show="(this.data?.memory?.used_percent || 0) < 30"
                             style="background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);"></div>
                    </div>
                    <!-- Percentage text overlay -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-lg font-bold text-gray-900 drop-shadow-[0_2px_4px_rgba(255,255,255,0.9)]" 
                              x-text="(100 - (this.data?.memory?.used_percent || 0)).toFixed(1) + '% FREE'"></span>
                    </div>
                </div>
                
                <div class="flex justify-between mt-3">
                    <div class="text-sm font-bold text-gray-900 drop-shadow-[0_1px_2px_rgba(255,255,255,0.8)]">
                        <span x-show="(this.data?.memory?.used_percent || 0) < 60">� Massive Headroom!</span>
                        <span x-show="(this.data?.memory?.used_percent || 0) >= 60 && (this.data?.memory?.used_percent || 0) < 80">⚡ Still Plenty Available</span>
                        <span x-show="(this.data?.memory?.used_percent || 0) >= 80">� Working Hard</span>
                    </div>
                    <div class="text-xs text-gray-700/80 font-medium drop-shadow-[0_1px_2px_rgba(255,255,255,0.6)]">
                        (Using <span x-text="(this.data?.memory?.used_gb || 0).toFixed(1) + ' GB'"></span>)
                    </div>
                </div>
            </div>

            <!-- Memory Details -->
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="bg-black/30 rounded p-2">
                    <div class="text-white/70">Available</div>
                    <div class="text-white font-semibold" x-text="(this.data?.memory?.available_gb || 0) + ' GB'"></div>
                </div>
                <div class="bg-black/30 rounded p-2">
                    <div class="text-white/70">Cached</div>
                    <div class="text-white font-semibold" x-text="(this.data?.memory?.cached_mb || 0) + ' MB'"></div>
                </div>
            </div>

            <!-- Swap -->
            <div x-show="this.data?.memory?.swap?.total_mb > 0" class="mt-3 pt-3 border-t border-white/10">
                <div class="flex justify-between items-center text-xs mb-2">
                    <span class="text-white/70">Swap</span>
              <span class="text-white font-semibold" 
                          x-text="(this.data?.memory?.swap?.used_mb || 0) + ' / ' + (this.data?.memory?.swap?.total_mb || 0) + ' MB'"></span>
                </div>
                <div class="h-2 bg-white/5 rounded-full overflow-hidden">
                    <div class="h-full bg-orange-500 transition-all duration-500" 
                         :style="{ width: ((this.data?.memory?.swap?.used_percent || 0) + '%') }"></div>
                </div>
            </div>
    </div>

    <!-- Disk & Network Grid -->
    <div class="grid grid-cols-2 gap-4">
            
            <!-- Disk Section -->
            <div x-show="this.data?.disk" class="bg-black/20 rounded-lg p-4 border border-white/10">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                    <h4 class="text-xs font-semibold text-white">Disk</h4>
                </div>
                
                <template x-for="(partition, idx) in (this.data?.disk?.partitions || [])" :key="partition.mount || idx">
                    <div class="mb-2">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-white/70" x-text="partition.mount"></span>
                            <span class="text-white font-semibold" x-text="partition.used_percent + '%'"></span>
                        </div>
                        <div class="h-2 bg-white/5 rounded-full overflow-hidden">
                            <div class="h-full bg-green-500 transition-all" 
                                 :style="{ width: (partition.used_percent + '%') }"></div>
                        </div>
                        <div class="text-xs text-white/60 mt-1" 
                             x-text="partition.used_gb + ' / ' + partition.total_gb + ' GB'"></div>
                    </div>
                </template>
            </div>

            <!-- Network Section -->
            <div x-show="this.data?.network" class="bg-black/20 rounded-lg p-4 border border-white/10">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                    <h4 class="text-xs font-semibold text-white">Network</h4>
                </div>
                
                <template x-for="(iface, idx) in (this.data?.network?.interfaces || [])" :key="iface.name || idx">
                    <div class="mb-3">
                        <div class="text-xs text-white/70 mb-1" x-text="iface.name"></div>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="bg-black/30 rounded p-2">
                                <div class="text-green-400">↓ RX</div>
                                <div class="text-white font-semibold" x-text="iface.rx_gb + ' GB'"></div>
                            </div>
                            <div class="bg-black/30 rounded p-2">
                                <div class="text-blue-400">↑ TX</div>
                                <div class="text-white font-semibold" x-text="iface.tx_gb + ' GB'"></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Processes -->
        <div x-show="data?.processes" class="bg-black/20 rounded-lg p-3 border border-white/10">
            <div class="flex items-center justify-between text-xs">
                <span class="text-white/70">Processes</span>
                <div class="flex gap-4">
                    <span class="text-white">Total: <span class="font-semibold" x-text="data?.processes?.total || 0"></span></span>
                    <span class="text-green-400">Running: <span class="font-semibold" x-text="data?.processes?.running || 0"></span></span>
                    <span class="text-red-400" x-show="data?.processes?.blocked > 0">
                        Blocked: <span class="font-semibold" x-text="data?.processes?.blocked || 0"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <script>
        function formatTimestamp(timestamp) {
            if (!timestamp) return '';
            const date = new Date(timestamp);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000); // seconds

            if (diff < 60) return `${diff}s ago`;
            if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
            return date.toLocaleTimeString('nb-NO', { hour: '2-digit', minute: '2-digit' });
        }
    </script>
</div>
