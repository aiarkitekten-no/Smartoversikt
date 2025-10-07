<div class="widget-card bg-gradient-to-br from-blue-600 to-indigo-700 text-white p-4 rounded-lg shadow-lg h-full flex flex-col"
     x-data="{
         ...widgetData('business.folio'),
         formatAmount(amount) {
             if (!amount) return '0,00';
             const num = parseFloat(String(amount).replace(/,/g, ''));
             return num.toLocaleString('nb-NO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
         },
         formatDate(dateString) {
             if (!dateString) return '';
             const date = new Date(dateString);
             const now = new Date();
             const diffDays = Math.floor((now - date) / (1000 * 60 * 60 * 24));
             if (diffDays === 0) return 'I dag';
             if (diffDays === 1) return 'I går';
             if (diffDays < 7) return `${diffDays} dager siden`;
             return date.toLocaleDateString('nb-NO', { day: 'numeric', month: 'short' });
         }
     }"
     x-init="init()">
    
    <!-- Widget Title -->
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-semibold flex items-center gap-2">
            <span class="text-2xl">💰</span>
            Folio Økonomi
        </h3>
    </div>

    <!-- Main Content -->
    <div class="flex-1 space-y-4">
        
        <!-- Balance Section -->
        <div class="bg-white bg-opacity-10 rounded-lg p-4 backdrop-blur-sm">
            <div class="text-xs text-white text-opacity-70 mb-1">Saldo</div>
            <template x-if="data?.balance">
                <div>
                    <div class="text-3xl font-bold" x-text="formatAmount(data.balance.total) + ' kr'"></div>
                    <div class="text-xs text-white text-opacity-60 mt-1">
                        <span x-text="data.balance.accounts_count"></span> kontoer
                    </div>
                </div>
            </template>
            <template x-if="!data?.balance">
                <div class="text-2xl font-bold text-white text-opacity-50">Laster...</div>
            </template>
        </div>

        <!-- Recent Transactions -->
        <div class="grid grid-cols-2 gap-3">
            
            <!-- Incoming Payments -->
            <div class="bg-white bg-opacity-10 rounded-lg p-3 backdrop-blur-sm">
                <div class="text-xs font-semibold text-green-300 mb-2 flex items-center gap-1">
                    <span>↓</span> Innbetalinger
                </div>
                <template x-if="data?.recent_incoming?.length > 0">
                    <div class="space-y-2">
                        <template x-for="tx in data.recent_incoming" :key="tx.date">
                            <div class="text-xs">
                                <div class="font-semibold text-green-200" x-text="formatAmount(tx.amount) + ' kr'"></div>
                                <div class="text-white text-opacity-70 truncate" x-text="tx.merchant"></div>
                                <div class="text-white text-opacity-50 text-[10px]" x-text="formatDate(tx.date)"></div>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="!data?.recent_incoming || data.recent_incoming.length === 0">
                    <div class="text-xs text-white text-opacity-50">Ingen nylige innbetalinger</div>
                </template>
            </div>

            <!-- Outgoing Payments -->
            <div class="bg-white bg-opacity-10 rounded-lg p-3 backdrop-blur-sm">
                <div class="text-xs font-semibold text-red-300 mb-2 flex items-center gap-1">
                    <span>↑</span> Utbetalinger
                </div>
                <template x-if="data?.recent_outgoing?.length > 0">
                    <div class="space-y-2">
                        <template x-for="tx in data.recent_outgoing" :key="tx.date">
                            <div class="text-xs">
                                <div class="font-semibold text-red-200" x-text="formatAmount(tx.amount) + ' kr'"></div>
                                <div class="text-white text-opacity-70 truncate" x-text="tx.merchant"></div>
                                <div class="text-white text-opacity-50 text-[10px]" x-text="formatDate(tx.date)"></div>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="!data?.recent_outgoing || data.recent_outgoing.length === 0">
                    <div class="text-xs text-white text-opacity-50">Ingen nylige utbetalinger</div>
                </template>
            </div>
        </div>

        <!-- Transaction Count -->
        <template x-if="data?.total_transactions">
            <div class="text-xs text-white text-opacity-60 text-center">
                <span x-text="data.total_transactions"></span> transaksjoner siste 30 dager
            </div>
        </template>
    </div>

    <!-- Status Footer -->
    <div class="flex items-center justify-between text-xs text-white text-opacity-70 pt-2 mt-2 border-t border-white border-opacity-20">
        <div class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full transition-colors duration-300"
                  :class="{
                      'bg-gray-400': statusLight === 'gray',
                      'bg-yellow-400 animate-pulse': statusLight === 'yellow',
                      'bg-green-400': statusLight === 'green',
                      'bg-red-400': statusLight === 'red'
                  }"></span>
            <span x-show="statusIcon" class="font-bold" x-text="statusIcon"></span>
            <span x-show="statusLight === 'yellow'">Oppdaterer...</span>
            <span x-show="statusLight === 'green'">Oppdatert</span>
            <span x-show="statusLight === 'red'">Feil</span>
        </div>
        <div>
            <span x-text="lastUpdate || 'Starter...'"></span>
        </div>
    </div>
</div>
