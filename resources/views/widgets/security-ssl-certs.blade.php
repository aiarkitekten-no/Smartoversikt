@php
    $summary = $data['summary'] ?? ['total' => 0, 'critical' => 0, 'warning' => 0, 'expired' => 0];
    
    // Determine overall status
    if ($summary['expired'] > 0 || $summary['critical'] > 0) {
        $status = 'critical';
        $statusText = 'KRITISK!';
        $bgClass = 'bg-gradient-to-br from-red-600 to-red-700';
    } elseif ($summary['warning'] > 0 || $summary['attention'] > 0) {
        $status = 'warning';
        $statusText = 'ADVARSEL';
        $bgClass = 'bg-gradient-to-br from-yellow-500 to-yellow-600';
    } else {
        $status = 'ok';
        $statusText = 'ALT OK';
        $bgClass = 'bg-gradient-to-br from-green-600 to-green-700';
    }
@endphp

<div 
    x-data="widgetData('{{ $widget->key ?? 'security.ssl-certs' }}')" 
    x-init="init()"
    class="h-full flex flex-col overflow-hidden shadow-sm sm:rounded-lg {{ $bgClass }}"
>
    <div class="p-2 flex-1 flex flex-col overflow-hidden">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                <span class="text-2xl">🔒</span>
                <h3 class="text-base font-semibold text-white drop-shadow-lg">SSL-sertifikater</h3>
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
                <!-- Summary -->
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-2 mb-2">
                    <div class="grid grid-cols-5 gap-1 text-center">
                        <div>
                            <div class="text-xs text-white text-opacity-70">Total</div>
                            <div class="text-lg font-bold text-white" x-text="data.summary?.total || 0"></div>
                        </div>
                        <div x-show="(data.summary?.expired || 0) > 0">
                            <div class="text-xs text-white text-opacity-70">Utløpt</div>
                            <div class="text-lg font-bold text-red-200" x-text="data.summary?.expired || 0"></div>
                        </div>
                        <div x-show="(data.summary?.critical || 0) > 0">
                            <div class="text-xs text-white text-opacity-70">Kritisk</div>
                            <div class="text-lg font-bold text-red-200" x-text="data.summary?.critical || 0"></div>
                        </div>
                        <div x-show="(data.summary?.warning || 0) > 0">
                            <div class="text-xs text-white text-opacity-70">Advarsel</div>
                            <div class="text-lg font-bold text-yellow-200" x-text="data.summary?.warning || 0"></div>
                        </div>
                        <div>
                            <div class="text-xs text-white text-opacity-70">OK</div>
                            <div class="text-lg font-bold text-green-200" x-text="data.summary?.ok || 0"></div>
                        </div>
                    </div>
                </div>

                <!-- Certificate List -->
                <div class="flex-1 overflow-y-auto space-y-1 pr-1" style="scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.3) transparent;">
                    <!-- Show only certificates that need attention (expires within 15 days or have issues) -->
                    <template x-data="{ needsAttention: (data.certificates || []).filter(c => c.status === 'expired' || c.status === 'critical' || c.status === 'warning' || c.status === 'error') }">
                        <template x-if="needsAttention.length === 0 && data.summary?.total > 0">
                            <!-- All OK - Compact view -->
                            <div class="text-center py-8">
                                <div class="text-6xl mb-4">✅</div>
                                <div class="text-2xl font-bold text-white mb-2">ALT OK</div>
                                <div class="text-lg text-white text-opacity-90" x-text="data.summary?.total + ' domener'"></div>
                                <div class="text-sm text-white text-opacity-70 mt-2">Alle sertifikater er gyldige</div>
                            </div>
                        </template>
                        
                        <template x-if="needsAttention.length > 0">
                            <!-- Show certificates that need attention -->
                            <template x-for="cert in needsAttention" :key="cert.domain">
                                <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-lg p-2 hover:bg-opacity-25 transition-all">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-1 mb-0.5">
                                                <span x-show="cert.status === 'expired'">🔴</span>
                                                <span x-show="cert.status === 'critical'">🟠</span>
                                                <span x-show="cert.status === 'warning'">🟡</span>
                                                <span x-show="cert.status === 'error'">⚠️</span>
                                                <div class="text-sm font-semibold text-white truncate" x-text="cert.domain"></div>
                                            </div>
                                            
                                            <template x-if="cert.status !== 'error'">
                                                <div class="text-xs text-white text-opacity-80">
                                                    <div>Utløper: <span x-text="cert.expiry_formatted"></span></div>
                                                    <div class="flex items-center gap-1">
                                                        <span x-text="Math.abs(cert.days_remaining)"></span>
                                                        <span x-show="cert.days_remaining >= 0">dager igjen</span>
                                                        <span x-show="cert.days_remaining < 0">dager siden</span>
                                                        <template x-if="cert.days_remaining < 0">
                                                            <span class="text-red-200 font-bold">UTLØPT!</span>
                                                        </template>
                                                    </div>
                                                    <div class="flex items-center gap-1 text-xs text-white text-opacity-60">
                                                        <span x-text="'Utsteder: ' + (cert.issuer || 'Unknown')"></span>
                                                        <span x-show="cert.auto_renew" class="text-green-300" title="Auto-fornyes av Let's Encrypt">🔄</span>
                                                    </div>
                                                    <div class="text-xs text-white text-opacity-50 mt-0.5">
                                                        <span x-show="cert.source === 'plesk'" title="Hentet fra Plesk">📋 Plesk</span>
                                                        <span x-show="cert.source === 'openssl'" title="Hentet via OpenSSL">🔐 OpenSSL</span>
                                                    </div>
                                                </div>
                                            </template>
                                            
                                            <template x-if="cert.status === 'error'">
                                                <div class="text-xs text-red-200" x-text="cert.error"></div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </template>
                    </template>
                    
                    <template x-if="!data.certificates || data.certificates.length === 0">
                        <div class="text-center py-8 text-white text-opacity-70 text-sm">
                            Ingen domener konfigurert.<br>
                            Legg til i <code class="text-xs bg-white bg-opacity-20 px-1 rounded">config/widgets.php</code>
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
