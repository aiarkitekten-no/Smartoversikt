@php
    $summary = $data['summary'] ?? ['total' => 0, 'last_hour' => 0, 'by_severity' => ['critical' => 0]];
    $riskScore = $data['risk_score'] ?? ['score' => 0, 'level' => 'LOW', 'color' => 'green'];
    
    // Determine overall status based on risk score
    if ($riskScore['level'] === 'CRITICAL') {
        $status = 'critical';
        $statusText = 'KRITISK!';
        $bgClass = 'bg-gradient-to-br from-red-700 to-red-800';
    } elseif ($riskScore['level'] === 'HIGH') {
        $status = 'warning';
        $statusText = 'HØY RISIKO';
        $bgClass = 'bg-gradient-to-br from-orange-600 to-orange-700';
    } elseif ($riskScore['level'] === 'MEDIUM') {
        $status = 'attention';
        $statusText = 'MODERAT';
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
                <!-- Summary Stats with Risk Score -->
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-2 mb-2">
                    <!-- Risk Score Banner -->
                    <template x-if="data.risk_score">
                        <div class="mb-2 p-2 rounded-lg"
                             :class="{
                                 'bg-red-600 bg-opacity-40': data.risk_score.level === 'CRITICAL',
                                 'bg-orange-500 bg-opacity-40': data.risk_score.level === 'HIGH',
                                 'bg-yellow-500 bg-opacity-40': data.risk_score.level === 'MEDIUM',
                                 'bg-green-600 bg-opacity-40': data.risk_score.level === 'LOW'
                             }">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs text-white text-opacity-70">Risikovurdering</div>
                                    <div class="text-xl font-bold text-white">
                                        <span x-text="data.risk_score.score"></span>/100
                                        <span class="text-sm ml-1" x-text="data.risk_score.level"></span>
                                    </div>
                                </div>
                                <div class="text-3xl" x-show="data.risk_score.level === 'CRITICAL'">🔴</div>
                                <div class="text-3xl" x-show="data.risk_score.level === 'HIGH'">🟠</div>
                                <div class="text-3xl" x-show="data.risk_score.level === 'MEDIUM'">🟡</div>
                                <div class="text-3xl" x-show="data.risk_score.level === 'LOW'">🟢</div>
                            </div>
                            <template x-if="data.risk_score.factors && data.risk_score.factors.length > 0">
                                <div class="mt-1 text-xs text-white text-opacity-90">
                                    <template x-for="(factor, idx) in data.risk_score.factors.slice(0, 2)" :key="idx">
                                        <div>• <span x-text="factor"></span></div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                    
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

                <!-- Analytics Sections (when we have data) -->
                <template x-if="data.analytics && (data.summary?.total > 0 || (data.fail2ban?.total_banned || 0) > 0)">
                    <div class="space-y-2 mb-2">
                        
                        <!-- #1: Top 5 Countries -->
                        <template x-if="data.analytics.top_countries && data.analytics.top_countries.length > 0">
                            <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-lg p-2">
                                <div class="text-xs font-semibold text-white mb-1.5">🌍 Top Angripende Land</div>
                                <div class="space-y-1">
                                    <template x-for="(country, idx) in data.analytics.top_countries" :key="country.code">
                                        <div class="flex items-center justify-between text-xs text-white text-opacity-90">
                                            <div class="flex items-center gap-1.5">
                                                <span x-text="country.flag" class="text-sm"></span>
                                                <span x-text="country.name"></span>
                                            </div>
                                            <span class="font-semibold" x-text="country.count + ' angrep'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- #3: Top 5 Attacking IPs -->
                        <template x-if="data.analytics.top_ips && data.analytics.top_ips.length > 0">
                            <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-lg p-2">
                                <div class="text-xs font-semibold text-white mb-1.5">🎯 Mest Aktive Angripere</div>
                                <div class="space-y-1">
                                    <template x-for="(item, idx) in data.analytics.top_ips" :key="item.ip">
                                        <div class="text-xs">
                                            <div class="flex items-center justify-between text-white text-opacity-90">
                                                <div class="flex items-center gap-1">
                                                    <span x-show="item.country && item.country.flag" x-text="item.country.flag"></span>
                                                    <span class="font-mono" x-text="item.ip"></span>
                                                </div>
                                                <span class="font-semibold" x-text="item.count + ' forsøk'"></span>
                                            </div>
                                            <template x-if="item.reputation && item.reputation.checked">
                                                <div class="mt-0.5 flex items-center gap-1">
                                                    <span class="inline-flex items-center gap-0.5 px-1 py-0.5 rounded text-xs"
                                                          :class="{
                                                              'bg-red-600 text-white': item.reputation.abuse_score >= 75,
                                                              'bg-orange-500 text-white': item.reputation.abuse_score >= 25 && item.reputation.abuse_score < 75,
                                                              'bg-green-600 text-white': item.reputation.abuse_score < 25
                                                          }">
                                                        <span x-show="item.reputation.abuse_score >= 75">⚠️</span>
                                                        <span x-show="item.reputation.abuse_score >= 25 && item.reputation.abuse_score < 75">⚡</span>
                                                        <span x-show="item.reputation.abuse_score < 25">✓</span>
                                                        <span x-text="item.reputation.abuse_score + '%'"></span>
                                                    </span>
                                                    <span class="text-white text-opacity-60" x-text="item.reputation.isp || ''"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- #4: Attack Type Distribution -->
                        <template x-if="data.analytics.attack_distribution && data.analytics.attack_distribution.length > 0">
                            <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-lg p-2">
                                <div class="text-xs font-semibold text-white mb-1.5">📊 Angrepstypefordeling</div>
                                <div class="space-y-1">
                                    <template x-for="attack in data.analytics.attack_distribution" :key="attack.type">
                                        <div class="flex items-center gap-2 text-xs text-white text-opacity-90">
                                            <span x-text="attack.icon"></span>
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <span x-text="attack.label"></span>
                                                    <span class="font-semibold" x-text="attack.percentage + '%'"></span>
                                                </div>
                                                <div class="mt-0.5 bg-white bg-opacity-20 rounded-full h-1.5 overflow-hidden">
                                                    <div class="bg-white h-full rounded-full" 
                                                         :style="'width: ' + attack.percentage + '%'"></div>
                                                </div>
                                            </div>
                                            <span class="text-xs text-white text-opacity-60" x-text="'(' + attack.count + ')'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- #5: Risk Factors (when risk score > 0) -->
                        <template x-if="data.risk_score && data.risk_score.factors && data.risk_score.factors.length > 0">
                            <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-lg p-2">
                                <div class="text-xs font-semibold text-white mb-1.5">⚠️ Risikofaktorer</div>
                                <div class="space-y-0.5">
                                    <template x-for="(factor, idx) in data.risk_score.factors" :key="idx">
                                        <div class="text-xs text-white text-opacity-90">• <span x-text="factor"></span></div>
                                    </template>
                                </div>
                                <template x-if="data.risk_score.recommendations && data.risk_score.recommendations.length > 0">
                                    <div class="mt-2 pt-2 border-t border-white border-opacity-20">
                                        <div class="text-xs font-semibold text-white mb-1">💡 Anbefalinger:</div>
                                        <div class="space-y-0.5">
                                            <template x-for="(rec, idx) in data.risk_score.recommendations" :key="idx">
                                                <div class="text-xs text-white text-opacity-80">• <span x-text="rec"></span></div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- #7: Most Targeted Services -->
                        <template x-if="data.analytics.targeted_services && data.analytics.targeted_services.length > 0">
                            <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-lg p-2">
                                <div class="text-xs font-semibold text-white mb-1.5">🎯 Mest Angrepne Tjenester</div>
                                <div class="space-y-1">
                                    <template x-for="service in data.analytics.targeted_services" :key="service.jail">
                                        <div class="flex items-center justify-between text-xs text-white text-opacity-90">
                                            <div class="flex items-center gap-1.5">
                                                <span x-text="service.icon"></span>
                                                <span x-text="service.name"></span>
                                            </div>
                                            <span class="font-semibold" 
                                                  :class="{
                                                      'text-red-200': service.severity === 'high',
                                                      'text-yellow-200': service.severity === 'medium'
                                                  }"
                                                  x-text="service.banned_count + ' blokkert'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- #8: Last Critical Event -->
                        <template x-if="data.analytics.last_critical">
                            <div class="bg-red-600 bg-opacity-30 backdrop-blur-sm rounded-lg p-2 border border-red-400 border-opacity-40">
                                <div class="text-xs font-semibold text-white mb-1">🔴 Siste Kritiske Hendelse</div>
                                <div class="text-xs text-white text-opacity-90">
                                    <div x-text="data.analytics.last_critical.message"></div>
                                    <div class="mt-1 flex items-center gap-2 text-white text-opacity-70">
                                        <span class="flex items-center gap-1">
                                            <template x-if="data.analytics.last_critical.country && data.analytics.last_critical.country.flag">
                                                <span x-text="data.analytics.last_critical.country.flag"></span>
                                            </template>
                                            <span class="font-mono" x-text="data.analytics.last_critical.ip"></span>
                                        </span>
                                        <span x-text="data.analytics.last_critical.relative_time"></span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- #9: Fail2ban Efficiency -->
                        <template x-if="data.analytics.fail2ban_efficiency && data.analytics.fail2ban_efficiency.enabled">
                            <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-lg p-2">
                                <div class="text-xs font-semibold text-white mb-1.5">🛡️ Fail2ban Effektivitet</div>
                                <div class="text-xs text-white text-opacity-90">
                                    <div class="flex items-center justify-between mb-1">
                                        <span>Blokkert:</span>
                                        <span class="font-semibold text-green-200" x-text="data.analytics.fail2ban_efficiency.blocked_percentage + '%'"></span>
                                    </div>
                                    <div class="bg-white bg-opacity-20 rounded-full h-2 overflow-hidden">
                                        <div class="bg-green-400 h-full rounded-full transition-all" 
                                             :style="'width: ' + data.analytics.fail2ban_efficiency.blocked_percentage + '%'"></div>
                                    </div>
                                    <div class="mt-1 text-white text-opacity-70" x-text="data.analytics.fail2ban_efficiency.message"></div>
                                </div>
                            </div>
                        </template>

                        <!-- #11: Attempted Usernames -->
                        <template x-if="data.analytics.attempted_usernames && data.analytics.attempted_usernames.length > 0">
                            <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-lg p-2">
                                <div class="text-xs font-semibold text-white mb-1.5">👤 Mest Prøvde Brukernavn (SSH)</div>
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="user in data.analytics.attempted_usernames" :key="user.username">
                                        <span class="text-xs bg-white bg-opacity-20 px-1.5 py-0.5 rounded text-white">
                                            <span class="font-mono" x-text="user.username"></span>
                                            <span class="text-white text-opacity-60" x-text="' (' + user.count + ')'"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </template>

                    </div>
                </template>

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
                                    
                                    <div class="flex items-center gap-2 text-xs text-white text-opacity-70 flex-wrap">
                                        <!-- IP with Country Flag -->
                                        <span class="flex items-center gap-1">
                                            <template x-if="event.country && event.country.flag">
                                                <span x-text="event.country.flag" class="text-sm"></span>
                                            </template>
                                            IP: <span class="font-mono" x-text="event.ip"></span>
                                            <template x-if="event.country && event.country.name && event.country.code !== 'XX'">
                                                <span class="text-white text-opacity-50" x-text="'(' + event.country.name + ')'"></span>
                                            </template>
                                        </span>
                                        
                                        <!-- User (if available) -->
                                        <template x-if="event.user">
                                            <span>Bruker: <span x-text="event.user"></span></span>
                                        </template>
                                        
                                        <!-- IP Reputation Badge -->
                                        <template x-if="event.reputation && event.reputation.checked">
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs"
                                                  :class="{
                                                      'bg-red-600 text-white': event.reputation.abuse_score >= 75,
                                                      'bg-orange-500 text-white': event.reputation.abuse_score >= 25 && event.reputation.abuse_score < 75,
                                                      'bg-green-600 text-white': event.reputation.abuse_score < 25
                                                  }">
                                                <span x-show="event.reputation.abuse_score >= 75">⚠️</span>
                                                <span x-show="event.reputation.abuse_score >= 25 && event.reputation.abuse_score < 75">⚡</span>
                                                <span x-show="event.reputation.abuse_score < 25">✓</span>
                                                <span x-text="'Abuse: ' + event.reputation.abuse_score + '%'"></span>
                                            </span>
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
