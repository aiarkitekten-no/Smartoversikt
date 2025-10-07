@php
    $summary = $data['summary'] ?? ['total' => 0, 'errors' => 0];
    
    // Determine overall status
    if ($summary['errors'] > 0) {
        $status = 'critical';
        $statusText = 'FEIL I JOBS';
        $bgClass = 'bg-gradient-to-br from-red-600 to-red-700';
    } elseif ($summary['total'] === 0) {
        $status = 'warning';
        $statusText = 'INGEN JOBS';
        $bgClass = 'bg-gradient-to-br from-yellow-600 to-yellow-700';
    } else {
        $status = 'ok';
        $statusText = 'KJØRER OK';
        $bgClass = 'bg-gradient-to-br from-blue-600 to-blue-700';
    }
@endphp

<div 
    x-data="widgetData('{{ $widget->key ?? 'system.cron-jobs' }}')" 
    x-init="init()"
    class="h-full flex flex-col overflow-hidden shadow-sm sm:rounded-lg {{ $bgClass }}"
>
    <div class="p-2 flex-1 flex flex-col overflow-hidden">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                <span class="text-2xl">⏰</span>
                <h3 class="text-base font-semibold text-white drop-shadow-lg">Scheduled Jobs</h3>
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
                    <div class="grid grid-cols-3 gap-2 text-center mb-2">
                        <div>
                            <div class="text-xs text-white text-opacity-70">Totalt</div>
                            <div class="text-xl font-bold text-white" x-text="data.summary?.total || 0"></div>
                        </div>
                        <div>
                            <div class="text-xs text-white text-opacity-70">Aktive</div>
                            <div class="text-xl font-bold text-green-200" x-text="data.summary?.active || 0"></div>
                        </div>
                        <div x-show="(data.summary?.errors || 0) > 0">
                            <div class="text-xs text-white text-opacity-70">Feil</div>
                            <div class="text-xl font-bold text-red-200" x-text="data.summary?.errors || 0"></div>
                        </div>
                    </div>
                    
                    <template x-if="data.summary?.next_job">
                        <div class="text-xs text-white text-opacity-80 text-center">
                            Neste: <span class="font-semibold" x-text="data.summary.next_job.command"></span>
                            <span x-text="data.summary.next_job.time"></span>
                        </div>
                    </template>
                    
                    <template x-if="data.last_run">
                        <div class="text-xs text-white text-opacity-70 text-center mt-1 pt-1 border-t border-white border-opacity-20">
                            Scheduler sist kjørt: <span x-text="data.last_run.relative"></span>
                            <span x-show="data.last_run.status === 'active'" class="text-green-200">✓</span>
                            <span x-show="data.last_run.status === 'stale'" class="text-yellow-200">⚠</span>
                        </div>
                    </template>
                </div>

                <!-- Jobs List -->
                <div class="flex-1 overflow-y-auto space-y-1 pr-1" style="scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.3) transparent;">
                    <template x-for="(job, index) in (data.jobs || [])" :key="index">
                        <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-lg p-2 hover:bg-opacity-25 transition-all">
                            <div class="flex items-start gap-2">
                                <span x-show="job.status === 'ok'">🟢</span>
                                <span x-show="job.status === 'error'">🔴</span>
                                <span x-show="job.status === 'warning'">🟡</span>
                                
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold text-white mb-0.5 truncate" x-text="job.command"></div>
                                    
                                    <div class="grid grid-cols-2 gap-x-2 text-xs text-white text-opacity-80">
                                        <div>
                                            <span class="text-opacity-60 text-white">Kjører:</span>
                                            <span x-text="job.cron_readable"></span>
                                        </div>
                                        <div>
                                            <span class="text-opacity-60 text-white">Neste:</span>
                                            <span x-text="job.next_run_relative || 'Ukjent'"></span>
                                        </div>
                                    </div>
                                    
                                    <template x-if="job.last_run_relative">
                                        <div class="text-xs text-white text-opacity-60 mt-0.5">
                                            Sist kjørt: <span x-text="job.last_run_relative"></span>
                                        </div>
                                    </template>
                                    
                                    <template x-if="job.status === 'error'">
                                        <div class="text-xs text-red-200 mt-1 font-semibold">
                                            ⚠️ Feil oppdaget i logg
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                    
                    <template x-if="!data.jobs || data.jobs.length === 0">
                        <div class="text-center py-8 text-white text-opacity-70 text-sm">
                            Ingen scheduled jobs funnet
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
