@php
    $summary = $data['summary'] ?? ['total' => 0, 'last_hour' => 0, 'by_severity' => ['critical' => 0]];
    
    // Determine overall status
    if ($summary['by_severity']['critical'] > 0) {
        $status = 'critical';
        $statusText = 'ANGREP!';
        $bgClass = 'bg-gradient-to-br from-red-700 to-red-800';
    } elseif ($summary['last_hour'] > 10) {
        $status = 'warning';
        $statusText = 'HØY AKTIVITET';
        $bgClass = 'bg-gradient-to-br from-orange-600 to-orange-700';
    } elseif ($summary['last_hour'] > 0) {
        $status = 'attention';
        $statusText = 'AKTIVITET';
        $bgClass = 'bg-gradient-to-br from-yellow-600 to-yellow-700';
    } else {
        $status = 'ok';
        $statusText = 'SIKKER';
        $bgClass = 'bg-gradient-to-br from-green-600 to-green-700';
    }
@endphp

<div 
    x-data="{
        ...widgetData('{{ $widget->key ?? 'security.events' }}'),
        blockingIp: null,
        async blockIp(ip, reason) {
            if (!confirm(`Blokker ${ip} i 2 timer?`)) return;
            
            this.blockingIp = ip;
            
            try {
                const response = await fetch('/api/security/block-ip', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
                    },
                    body: JSON.stringify({ ip, reason })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(`✅ ${result.message}`);
                    this.fetchData(); // Refresh widget
                } else {
                    alert(`❌ Feil: ${result.message}`);
                }
            } catch (error) {
                alert(`❌ Kunne ikke blokkere IP: ${error.message}`);
            } finally {
                this.blockingIp = null;
            }
        }
    }" 
    x-init="init()"
    class="h-full flex flex-col overflow-hidden shadow-sm sm:rounded-lg {{ $bgClass }}"
>
    <div class="p-2 flex-1 flex flex-col overflow-hidden">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                <span class="text-2xl">🛡️</span>
                <h3 class="text-base font-semibold text-white drop-shadow-lg">Security Events</h3>
            </div>
            <div class="flex items-center gap-1">
                <span x-show="loading" class="inline-block w-2 h-2 bg-white rounded-full animate-pulse" title="Laster..."></span>
                <span x-show="!loading && !error" class="inline-block w-2 h-2 bg-white rounded-full" title="Live"></span>
                <span x-show="error" class="inline-block w-2 h-2 bg-red-900 rounded-full" title="Feil"></span>
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
                            <div class="text-lg font-semibold text-red-200" x-text="data.summary?.by_severity?.critical || 0"></div>
                        </div>
                        <div>
                            <div class="text-xs text-white text-opacity-70">Unike IP</div>
                            <div class="text-lg font-semibold text-white text-opacity-80" x-text="data.summary?.unique_ip_count || 0"></div>
                        </div>
                    </div>
                    
                    <!-- Fail2ban Status -->
                    <template x-if="data.fail2ban?.installed">
                        <div class="pt-2 border-t border-white border-opacity-20">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-1">
                                    <span>🔒 Fail2ban:</span>
                                    <span x-show="data.fail2ban.running" class="text-green-200">Aktiv</span>
                                    <span x-show="!data.fail2ban.running" class="text-red-200">Inaktiv</span>
                                </div>
                                <div class="text-white font-semibold">
                                    <span x-text="data.fail2ban.total_banned || 0"></span> IP bannlyst
                                </div>
                            </div>
                            
                            <template x-if="data.fail2ban.jails && data.fail2ban.jails.length > 0">
                                <div class="mt-1 flex flex-wrap gap-1">
                                    <template x-for="jail in data.fail2ban.jails" :key="jail.name">
                                        <span class="text-xs bg-white bg-opacity-20 px-1.5 py-0.5 rounded">
                                            <span x-text="jail.name"></span>: <span x-text="jail.banned"></span>
                                        </span>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Events List -->
                <div class="flex-1 overflow-y-auto space-y-1 pr-1" style="scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.3) transparent;">
                    <template x-for="(event, index) in (data.events || [])" :key="index">
                        <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-lg p-2 hover:bg-opacity-25 transition-all">
                            <div class="flex items-start gap-2">
                                <span x-show="event.severity === 'critical'">🔴</span>
                                <span x-show="event.severity === 'warning'">🟡</span>
                                <span x-show="event.severity === 'info'">🔵</span>
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <span class="text-xs font-semibold text-white px-1.5 py-0.5 rounded" 
                                              :class="{
                                                  'bg-red-500': event.severity === 'critical',
                                                  'bg-yellow-500': event.severity === 'warning',
                                                  'bg-blue-500': event.severity === 'info'
                                              }"
                                              x-text="event.type === 'ssh_failed_login' ? 'SSH' : 
                                                     event.type === 'web_auth_failure' ? 'WEB' : 
                                                     event.type === 'suspicious_request' ? 'ATTACK' : 'OTHER'"></span>
                                        <span class="text-xs text-white text-opacity-50 ml-auto" x-text="event.relative_time"></span>
                                    </div>
                                    
                                    <div class="text-xs text-white text-opacity-90 mb-0.5" x-text="event.message"></div>
                                    
                                    <div class="flex items-center gap-2 text-xs text-white text-opacity-70">
                                        <span>IP: <span class="font-mono" x-text="event.ip"></span></span>
                                        <template x-if="event.user">
                                            <span>Bruker: <span x-text="event.user"></span></span>
                                        </template>
                                    </div>

                                    <!-- Block IP Button (for critical events) -->
                                    <template x-if="event.severity === 'critical' || event.type === 'suspicious_request'">
                                        <button 
                                            @click="blockIp(event.ip, event.message)"
                                            :disabled="blockingIp === event.ip"
                                            class="mt-2 px-2 py-1 text-xs font-semibold rounded bg-red-600 hover:bg-red-700 text-white disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center gap-1"
                                        >
                                            <span x-show="blockingIp !== event.ip">🚫 Blokker i Firewall (2t)</span>
                                            <span x-show="blockingIp === event.ip">⏳ Blokkerer...</span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                    
                    <template x-if="!data.events || data.events.length === 0">
                        <div class="text-center py-8 text-white text-opacity-90 text-sm">
                            <div class="text-4xl mb-2">🎉</div>
                            <div class="font-semibold">Ingen sikkerhetshendelser!</div>
                            <div class="text-xs text-white text-opacity-70 mt-1">Alt ser bra ut</div>
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
