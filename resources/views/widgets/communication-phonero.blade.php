<div class="widget-card bg-gradient-to-br from-blue-700 to-indigo-800 text-white p-4 rounded-lg shadow-lg h-full flex flex-col"
     x-data="{
         ...widgetData('communication.phonero'),
         phoneNumber: '',
         calling: false,
         callStatus: null,
         
         async makeCall() {
             if (!this.phoneNumber) {
                 this.callStatus = { success: false, message: 'Angi telefonnummer' };
                 return;
             }
             
             this.calling = true;
             this.callStatus = { status: 'calling', message: 'Ringer...' };
             
             try {
                 const response = await fetch('/api/phonero/call', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                     },
                     body: JSON.stringify({
                         destination_number: this.phoneNumber
                     })
                 });
                 
                 const result = await response.json();
                 this.callStatus = result;
                 
                 if (result.success) {
                     setTimeout(() => {
                         this.callStatus = { status: 'connected', message: 'Samtale pågår...' };
                     }, 3000);
                     
                     setTimeout(() => {
                         this.callStatus = null;
                         this.phoneNumber = '';
                         this.calling = false;
                     }, 10000);
                 } else {
                     this.callStatus = { 
                         success: false, 
                         status: 'failed', 
                         message: result.error || 'Ukjent feil'
                     };
                     setTimeout(() => {
                         this.callStatus = null;
                         this.calling = false;
                     }, 5000);
                 }
                 
             } catch (error) {
                 this.callStatus = { 
                     success: false, 
                     status: 'failed', 
                     message: 'Nettverksfeil: ' + error.message
                 };
                 setTimeout(() => {
                     this.callStatus = null;
                     this.calling = false;
                 }, 5000);
             }
         },
         
         formatCallTime(timestamp) {
             if (!timestamp) return '';
             const date = new Date(timestamp);
             const now = new Date();
             const diffMinutes = Math.floor((now - date) / 60000);
             
             if (diffMinutes < 1) return 'Nå';
             if (diffMinutes < 60) return `${diffMinutes} min siden`;
             if (diffMinutes < 1440) return `${Math.floor(diffMinutes / 60)} timer siden`;
             return date.toLocaleDateString('nb-NO', { day: 'numeric', month: 'short' });
         },
         
         formatDuration(seconds) {
             if (!seconds || seconds === 0) return '';
             const mins = Math.floor(seconds / 60);
             const secs = seconds % 60;
             return mins > 0 ? `${mins}m ${secs}s` : `${secs}s`;
         },
         
         getResultIcon(result) {
             if (result === 'ANSWERED') return '✅';
             if (result === 'MISSED') return '❌';
             return '⏳';
         },
         
         getResultColor(result) {
             if (result === 'ANSWERED') return 'text-green-300';
             if (result === 'MISSED') return 'text-red-300';
             return 'text-yellow-300';
         }
     }"
     x-init="init()">
    
    <!-- Widget Title -->
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-semibold flex items-center gap-2">
            <span class="text-2xl">☎️</span>
            Phonero Telefon
        </h3>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col space-y-3 overflow-hidden">
        
        <!-- Click-to-Dial Section -->
        <div class="bg-white bg-opacity-10 rounded-lg p-3 backdrop-blur-sm">
            <label class="text-xs text-white text-opacity-70 mb-2 block">Ring opp</label>
            <div class="flex gap-2">
                <input 
                    type="text" 
                    x-model="phoneNumber"
                    placeholder="Telefonnummer"
                    class="flex-1 px-3 py-2 bg-white bg-opacity-20 backdrop-blur-sm border border-white border-opacity-30 rounded text-white placeholder-white placeholder-opacity-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
                    :disabled="calling"
                    @keyup.enter="makeCall()"
                />
                <button 
                    @click="makeCall()"
                    :disabled="calling || !phoneNumber"
                    class="px-4 py-2 rounded font-semibold transition-all whitespace-nowrap"
                    :class="calling ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-500'"
                >
                    <span x-show="!calling">📞 RING</span>
                    <span x-show="calling">⏳</span>
                </button>
            </div>
            
            <!-- Call Status -->
            <div x-show="callStatus" class="mt-2">
                <div 
                    x-show="callStatus?.status === 'calling'"
                    class="bg-yellow-600 bg-opacity-50 backdrop-blur-sm border border-yellow-300 rounded px-3 py-2 text-sm flex items-center gap-2"
                >
                    <span class="text-xl">📞</span>
                    <span class="font-semibold">Ringer...</span>
                </div>
                
                <div 
                    x-show="callStatus?.status === 'connected'"
                    class="bg-green-600 bg-opacity-50 backdrop-blur-sm border border-green-300 rounded px-3 py-2 text-sm flex items-center gap-2"
                >
                    <span class="text-xl">✅</span>
                    <span class="font-semibold">Samtale pågår</span>
                </div>
                
                <div 
                    x-show="callStatus?.success === false"
                    class="bg-red-600 bg-opacity-50 backdrop-blur-sm border border-red-300 rounded px-3 py-2 text-sm"
                >
                    <div class="flex items-center gap-2">
                        <span class="text-xl">❌</span>
                        <div class="flex-1">
                            <div class="font-semibold">Feil ved oppringing</div>
                            <div class="text-xs opacity-90" x-text="callStatus?.message || 'Ukjent feil'"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Calls -->
        <div class="bg-white bg-opacity-10 rounded-lg p-3 backdrop-blur-sm flex-1 overflow-hidden flex flex-col">
            <div class="text-xs font-semibold text-white text-opacity-90 mb-2">Siste samtaler</div>
            <div class="flex-1 overflow-y-auto space-y-1.5" style="scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.3) transparent;">
                <template x-if="data?.recent_calls && data.recent_calls.length > 0">
                    <div>
                        <template x-for="call in data.recent_calls" :key="call.timestamp">
                            <div class="bg-white bg-opacity-10 rounded p-2 text-xs">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-1.5">
                                        <span :class="getResultColor(call.result)" x-text="getResultIcon(call.result)"></span>
                                        <span class="font-semibold" x-text="call.from || 'Ukjent'"></span>
                                    </div>
                                    <span class="text-white text-opacity-60 text-[10px]" x-text="formatCallTime(call.timestamp)"></span>
                                </div>
                                <div class="flex items-center justify-between text-white text-opacity-70 text-[10px]">
                                    <span x-text="call.result === 'ANSWERED' ? 'Besvart' : call.result === 'MISSED' ? 'Tapt' : call.result"></span>
                                    <span x-show="call.duration > 0" x-text="formatDuration(call.duration)"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="!data?.recent_calls || data.recent_calls.length === 0">
                    <div class="text-xs text-white text-opacity-50 text-center py-4">
                        Ingen samtaler ennå
                    </div>
                </template>
            </div>
        </div>

        <!-- Queue Status -->
        <div class="bg-white bg-opacity-10 rounded-lg p-3 backdrop-blur-sm">
            <template x-if="data?.queue_status">
                <div>
                    <div class="text-xs font-semibold text-white text-opacity-90 mb-2">
                        <span>Kø:</span> <span x-text="data.queue_status.name"></span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <div class="text-white text-opacity-60 text-[10px]">Agenter</div>
                            <div class="font-semibold">
                                <span x-text="data.queue_status.ready_members"></span>/<span x-text="data.queue_status.members_count"></span> klar
                            </div>
                        </div>
                        <div>
                            <div class="text-white text-opacity-60 text-[10px]">Kortnummer</div>
                            <div class="font-semibold" x-text="data.queue_status.short_number || '-'"></div>
                        </div>
                    </div>
                </div>
            </template>
            <template x-if="!data?.queue_status">
                <div class="text-xs text-white text-opacity-50">
                    Ingen køinformasjon
                </div>
            </template>
        </div>
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
            <template x-if="data?.main_numbers">
                <span class="text-[10px]">
                    <span x-text="data.main_numbers.join(' / ')"></span>
                </span>
            </template>
        </div>
        <div>
            <span x-text="lastUpdate || 'Starter...'"></span>
        </div>
    </div>
</div>
