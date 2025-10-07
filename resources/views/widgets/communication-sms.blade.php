<div class="widget-card bg-gradient-to-br from-green-600 to-emerald-700 text-white p-4 rounded-lg shadow-lg h-full flex flex-col"
     x-data="{
         ...widgetData('communication.sms'),
         toNumber: '',
         message: '',
         sending: false,
         lastResult: null,
         totalSent: 0,
         
         async sendSms() {
             if (!this.toNumber || !this.message) {
                 this.lastResult = { success: false, message: 'Fyll ut alle felt' };
                 return;
             }
             
             this.sending = true;
             this.lastResult = null;
             
             try {
                 const response = await fetch('/api/sms/send', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                     },
                     body: JSON.stringify({
                         to: this.toNumber,
                         message: this.message
                     })
                 });
                 
                 const result = await response.json();
                 this.lastResult = result;
                 
                 if (result.success) {
                     // Increment sent counter
                     this.totalSent += (result.credits_used || 1);
                     
                     // Clear form on success
                     setTimeout(() => {
                         this.toNumber = '';
                         this.message = '';
                     }, 2000);
                 }
                 
             } catch (error) {
                 this.lastResult = { success: false, message: 'Nettverksfeil: ' + error.message };
             } finally {
                 this.sending = false;
             }
         }
     }"
     x-init="init()">
    
    <!-- Widget Title -->
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-semibold flex items-center gap-2">
            <span class="text-2xl">📱</span>
            Send SMS
        </h3>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col space-y-3">
        
        <!-- Phone Number Input -->
        <div>
            <label class="text-xs text-white text-opacity-70 mb-1 block">Til nummer</label>
            <input 
                type="text" 
                x-model="toNumber"
                placeholder="41347577 eller +47 413 47 577"
                class="w-full px-3 py-2 bg-white bg-opacity-20 backdrop-blur-sm border border-white border-opacity-30 rounded text-white placeholder-white placeholder-opacity-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
                :disabled="sending"
            />
        </div>

        <!-- Message Textarea -->
        <div class="flex-1 flex flex-col">
            <label class="text-xs text-white text-opacity-70 mb-1 block flex items-center justify-between">
                <span>Melding</span>
                <span x-text="message.length + '/160'" class="text-white text-opacity-60 text-[10px]"></span>
            </label>
            <textarea 
                x-model="message"
                placeholder="Skriv melding her..."
                rows="4"
                maxlength="160"
                class="flex-1 w-full px-3 py-2 bg-white bg-opacity-20 backdrop-blur-sm border border-white border-opacity-30 rounded text-white placeholder-white placeholder-opacity-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 resize-none"
                :disabled="sending"
            ></textarea>
        </div>

        <!-- Send Button -->
        <button 
            @click="sendSms()"
            :disabled="sending || !toNumber || !message"
            class="w-full py-2 px-4 rounded font-semibold transition-all"
            :class="sending ? 'bg-gray-400 cursor-not-allowed' : 'bg-white text-green-700 hover:bg-green-50'"
        >
            <span x-show="!sending">📤 Send SMS</span>
            <span x-show="sending" class="flex items-center justify-center gap-2">
                <span class="inline-block w-4 h-4 border-2 border-green-700 border-t-transparent rounded-full animate-spin"></span>
                Sender...
            </span>
        </button>

        <!-- Result Message -->
        <div x-show="lastResult" class="mt-2">
            <div 
                x-show="lastResult?.success === true"
                class="bg-green-800 bg-opacity-50 backdrop-blur-sm border border-green-300 rounded-lg p-3 text-sm flex items-center gap-2"
            >
                <span class="text-2xl">✅</span>
                <div>
                    <div class="font-semibold">Sendt!</div>
                    <div class="text-xs text-white text-opacity-80" x-text="lastResult?.message"></div>
                </div>
            </div>
            
            <div 
                x-show="lastResult?.success === false"
                class="bg-red-800 bg-opacity-50 backdrop-blur-sm border border-red-300 rounded-lg p-3 text-sm flex items-center gap-2"
            >
                <span class="text-2xl">❌</span>
                <div>
                    <div class="font-semibold">Feil!</div>
                    <div class="text-xs text-white text-opacity-80" x-text="lastResult?.error || lastResult?.message"></div>
                </div>
            </div>
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
            <span>Klar</span>
        </div>
        <div class="flex items-center gap-2">
            <template x-if="totalSent > 0">
                <span class="font-semibold text-green-200">
                    � <span x-text="totalSent"></span> sendt
                </span>
            </template>
            <span class="text-[10px] text-white text-opacity-50">SMStools</span>
        </div>
    </div>
</div>
