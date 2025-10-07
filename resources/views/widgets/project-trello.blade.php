@php
    $summary = $data['summary'] ?? ['total' => 0, 'overdue' => 0, 'due_today' => 0];
    
    // Determine overall status
    if ($summary['overdue'] > 0) {
        $status = 'critical';
        $statusText = 'FORSINKELSER!';
        $bgClass = 'bg-gradient-to-br from-red-600 to-red-700';
    } elseif ($summary['due_today'] > 0) {
        $status = 'warning';
        $statusText = 'OPPGAVER I DAG';
        $bgClass = 'bg-gradient-to-br from-orange-500 to-orange-600';
    } elseif ($summary['due_this_week'] > 0) {
        $status = 'attention';
        $statusText = 'DENNE UKEN';
        $bgClass = 'bg-gradient-to-br from-blue-500 to-blue-600';
    } else {
        $status = 'ok';
        $statusText = 'ALT OK';
        $bgClass = 'bg-gradient-to-br from-green-600 to-green-700';
    }
@endphp

<div 
    x-data="widgetData('{{ $widget->key ?? 'project.trello' }}')" 
    x-init="init()"
    class="h-full flex flex-col overflow-hidden shadow-sm sm:rounded-lg {{ $bgClass }}"
>
    <div class="p-2 flex-1 flex flex-col overflow-hidden">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                <span class="text-2xl">📋</span>
                <h3 class="text-base font-semibold text-white drop-shadow-lg">Trello Oppgaver</h3>
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
                <!-- List Summary Stats -->
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-3 mb-2">
                    <div class="grid grid-cols-5 gap-2 text-center text-xs">
                        <div>
                            <div class="text-white text-opacity-70 text-[10px]">Planlagt</div>
                            <div class="text-xl font-bold text-white" x-text="data.by_list?.['Planlagt'] || 0"></div>
                        </div>
                        <div>
                            <div class="text-white text-opacity-70 text-[10px]">Pågår</div>
                            <div class="text-xl font-bold text-yellow-200" x-text="data.by_list?.['Pågår'] || 0"></div>
                        </div>
                        <div>
                            <div class="text-white text-opacity-70 text-[10px]">Ferdig</div>
                            <div class="text-xl font-bold text-green-200" x-text="data.by_list?.['Ferdig'] || 0"></div>
                        </div>
                        <div>
                            <div class="text-white text-opacity-70 text-[10px]">Bugs</div>
                            <div class="text-xl font-bold text-red-200" x-text="data.by_list?.['Bugs'] || 0"></div>
                        </div>
                        <div>
                            <div class="text-white text-opacity-70 text-[10px]">Ønsker</div>
                            <div class="text-xl font-bold text-purple-200" x-text="data.by_list?.['Ønsker'] || 0"></div>
                        </div>
                    </div>
                </div>

                <!-- Cards List -->
                <div class="flex-1 overflow-y-auto space-y-2 pr-1" style="scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.3) transparent;">
                    
                    <!-- Overdue Cards -->
                    <template x-if="data.overdue && data.overdue.length > 0">
                        <div>
                            <div class="text-xs font-semibold text-red-200 mb-1 flex items-center gap-1">
                                <span>🔴</span>
                                <span>FORSINKEDE OPPGAVER</span>
                            </div>
                            <div class="space-y-1">
                                <template x-for="card in data.overdue" :key="card.id">
                                    <a :href="card.url" target="_blank" class="block bg-red-800 bg-opacity-40 backdrop-blur-sm rounded p-2 hover:bg-opacity-60 transition-all">
                                        <div class="text-sm font-semibold text-white truncate" x-text="card.name"></div>
                                        <div class="text-xs text-white text-opacity-80 mt-0.5">
                                            <span x-text="card.list"></span>
                                            <span class="mx-1">•</span>
                                            <span class="text-red-200 font-semibold" x-text="card.days_late + ' dager forsinket'"></span>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Due Today -->
                    <template x-if="data.due_today && data.due_today.length > 0">
                        <div>
                            <div class="text-xs font-semibold text-yellow-200 mb-1 flex items-center gap-1">
                                <span>📅</span>
                                <span>FORFALLER I DAG</span>
                            </div>
                            <div class="space-y-1">
                                <template x-for="card in data.due_today" :key="card.id">
                                    <a :href="card.url" target="_blank" class="block bg-white bg-opacity-15 backdrop-blur-sm rounded p-2 hover:bg-opacity-25 transition-all">
                                        <div class="text-sm font-semibold text-white truncate" x-text="card.name"></div>
                                        <div class="text-xs text-white text-opacity-80 mt-0.5">
                                            <span x-text="card.list"></span>
                                            <span class="mx-1">•</span>
                                            <span x-text="'Kl. ' + card.due_time"></span>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Due This Week -->
                    <template x-if="data.due_this_week && data.due_this_week.length > 0 && (!data.overdue || data.overdue.length === 0) && (!data.due_today || data.due_today.length === 0)">
                        <div>
                            <div class="text-xs font-semibold text-blue-200 mb-1 flex items-center gap-1">
                                <span>📆</span>
                                <span>DENNE UKEN</span>
                            </div>
                            <div class="space-y-1">
                                <template x-for="card in data.due_this_week.slice(0, 5)" :key="card.id">
                                    <a :href="card.url" target="_blank" class="block bg-white bg-opacity-10 backdrop-blur-sm rounded p-2 hover:bg-opacity-20 transition-all">
                                        <div class="text-sm font-semibold text-white truncate" x-text="card.name"></div>
                                        <div class="text-xs text-white text-opacity-70 mt-0.5">
                                            <span x-text="card.list"></span>
                                            <span class="mx-1">•</span>
                                            <span x-text="card.due_relative"></span>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- All OK State -->
                    <template x-if="(!data.overdue || data.overdue.length === 0) && (!data.due_today || data.due_today.length === 0) && (!data.due_this_week || data.due_this_week.length === 0)">
                        <div class="text-center py-8">
                            <div class="text-6xl mb-4">✅</div>
                            <div class="text-2xl font-bold text-white mb-2">ALT OK</div>
                            <div class="text-sm text-white text-opacity-80">
                                Ingen oppgaver med nært forestående frist
                            </div>
                            <template x-if="data.summary?.completed_today > 0">
                                <div class="text-lg text-green-200 mt-4">
                                    🎉 <span x-text="data.summary.completed_today"></span> fullført i dag!
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Completed Today (if any) -->
                    <template x-if="data.completed_today && data.completed_today.length > 0 && (data.overdue?.length > 0 || data.due_today?.length > 0)">
                        <div class="pt-2 border-t border-white border-opacity-20">
                            <div class="text-xs font-semibold text-green-200 mb-1">
                                ✅ Fullført i dag (<span x-text="data.completed_today.length"></span>)
                            </div>
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
