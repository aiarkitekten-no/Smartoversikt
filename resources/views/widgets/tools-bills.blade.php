<div class="widget-card bg-gradient-to-br from-purple-600 to-indigo-700 text-white p-4 rounded-lg shadow-lg h-full flex flex-col"
     x-data="{
         ...widgetData('tools.bills'),
         showModal: false,
         showThisMonth: true,
         editingBill: null,
         newBill: {
             name: '',
             amount: '',
             due_day: ''
         },
         
         async togglePaid(billId) {
             try {
                 const response = await fetch(`/api/bills/${billId}/toggle-paid`, {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'Accept': 'application/json',
                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                     }
                 });
                 
                 const result = await response.json();
                 
                 if (result.success) {
                     // Refresh widget data
                     await this.fetchData();
                 }
             } catch (error) {
                 console.error('Toggle paid failed:', error);
             }
         },
         
         async createBill() {
             if (!this.newBill.name || !this.newBill.amount || !this.newBill.due_day) {
                 alert('Fyll ut alle feltene');
                 return;
             }
             
             try {
                 const response = await fetch('/api/bills', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'Accept': 'application/json',
                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                     },
                     body: JSON.stringify(this.newBill)
                 });
                 
                 const result = await response.json();
                 
                 if (result.success) {
                     this.newBill = { name: '', amount: '', due_day: '' };
                     this.showModal = false;
                     await this.fetchData();
                 } else {
                     alert('Feil: ' + (result.error || 'Ukjent feil'));
                 }
             } catch (error) {
                 alert('Nettverksfeil: ' + error.message);
             }
         },
         
         async deleteBill(billId, billName) {
             if (!confirm(`Slette '${billName}'?`)) return;
             
             try {
                 const response = await fetch(`/api/bills/${billId}`, {
                     method: 'DELETE',
                     headers: {
                         'Accept': 'application/json',
                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                     }
                 });
                 
                 const result = await response.json();
                 
                 if (result.success) {
                     await this.fetchData();
                 }
             } catch (error) {
                 console.error('Delete failed:', error);
             }
         },
         
         getFilteredBills() {
             if (!this.data?.bills) {
                 return [];
             }
             
             const today = new Date().getDate();
             
             return this.data.bills.filter(bill => {
                 if (this.showThisMonth) {
                     // This month: Show bills that haven't passed OR are due within 7 days
                     return bill.due_day >= today || bill.days_until_due <= 7;
                 } else {
                     // Next month: Show bills where due date has passed this month
                     return bill.due_day < today && bill.days_until_due > 7;
                 }
             }).sort((a, b) => a.days_until_due - b.days_until_due);
         },
         
         getUrgencyColor(level) {
             if (level === 'red') return 'bg-red-500';
             if (level === 'yellow') return 'bg-yellow-500';
             return 'bg-green-500';
         },
         
         getProgressWidth(days) {
             // 0 days = 0%, 30 days = 100%
             return Math.min(100, (days / 30) * 100);
         }
     }"
     x-init="init()">
    
    <!-- Widget Header -->
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-semibold flex items-center gap-2">
            <span class="text-2xl">💳</span>
            Forfall
        </h3>
        
        <!-- New Button (centered) -->
        <button 
            @click="showModal = true"
            class="px-3 py-1 bg-white bg-opacity-20 hover:bg-opacity-30 rounded text-sm font-semibold transition-all"
        >
            + Ny
        </button>
        
        <!-- Month Toggle -->
        <button 
            @click="showThisMonth = !showThisMonth"
            class="px-2 py-1 text-xs rounded transition-colors"
            :class="showThisMonth ? 'bg-white bg-opacity-30' : 'bg-white bg-opacity-10 hover:bg-opacity-20'"
        >
            <span x-show="showThisMonth">📅 Denne</span>
            <span x-show="!showThisMonth">📅 Neste</span>
        </button>
    </div>

    <!-- Bills List -->
    <div class="flex-1 overflow-y-auto pr-1" style="scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.3) transparent;">
        <div class="space-y-2">
            <template x-for="bill in getFilteredBills()" :key="bill.id">
                <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-lg p-3 hover:bg-opacity-25 transition-all">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <!-- Urgency Badge -->
                            <div class="flex items-center gap-1 px-2 py-0.5 rounded text-xs font-bold"
                                 :class="getUrgencyColor(bill.urgency_level)">
                                <span x-text="bill.days_until_due"></span>
                                <span class="text-[10px]">dg</span>
                            </div>
                            <!-- Name -->
                            <span class="font-semibold" x-text="bill.name"></span>
                        </div>
                        <!-- Amount -->
                        <div class="text-sm font-bold" x-text="bill.formatted_amount"></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- Paid Checkbox -->
                        <input 
                            type="checkbox" 
                            :checked="bill.is_paid_this_month"
                            @change="togglePaid(bill.id)"
                            class="w-5 h-5 rounded cursor-pointer"
                            title="Marker som betalt"
                        />
                        <!-- Delete Button -->
                        <button 
                            @click="deleteBill(bill.id, bill.name)"
                            class="text-red-300 hover:text-red-100 text-xl leading-none"
                            title="Slett"
                        >
                            ×
                        </button>
                    </div>
                </div>
                
                <!-- Progress Bar -->
                <div class="w-full bg-white bg-opacity-20 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full transition-all"
                         :class="getUrgencyColor(bill.urgency_level)"
                         :style="'width: ' + getProgressWidth(bill.days_until_due) + '%'">
                    </div>
                </div>
                <div class="text-[10px] text-white text-opacity-60 mt-1">
                    Forfaller: <span x-text="bill.due_day + '.'"></span>
                </div>
            </div>
        </template>
        </div>
        
        <!-- Empty State -->
        <div x-show="!loading && getFilteredBills().length === 0" class="text-center py-8 text-white text-opacity-60">
            <div class="text-4xl mb-2">📋</div>
            <div class="text-sm">Ingen forfall <span x-text="showThisMonth ? 'denne måneden' : 'neste måned'"></span></div>
        </div>
    </div>

    <!-- Totals Footer -->
    <div x-show="data?.totals" class="mt-3 pt-3 border-t border-white border-opacity-20 text-sm space-y-1">
        <div class="flex justify-between">
            <span class="text-white text-opacity-70">Utgift/mnd totalt:</span>
            <span class="font-bold" x-text="data?.totals?.formatted_monthly_total"></span>
        </div>
        <div class="flex justify-between">
            <span class="text-white text-opacity-70">Rest denne måned:</span>
            <span class="font-bold text-yellow-300" x-text="data?.totals?.formatted_remaining"></span>
        </div>
        <div class="flex justify-between text-xs text-white text-opacity-50">
            <span>Betalt:</span>
            <span x-text="(data?.totals?.paid_count || 0) + ' / ' + (data?.totals?.total_count || 0)"></span>
        </div>
    </div>

    <!-- Status Footer -->
    <div class="mt-2 flex items-center justify-between text-xs">
        <div class="flex items-center gap-1">
            <span class="h-2 w-2 rounded-full"
                  :class="{
                      'bg-gray-400': statusLight === 'gray',
                      'bg-yellow-400': statusLight === 'yellow',
                      'bg-green-400': statusLight === 'green',
                      'bg-red-400': statusLight === 'red'
                  }"></span>
            <span x-show="statusIcon" class="font-bold" x-text="statusIcon"></span>
        </div>
        <div>
            <span x-text="lastUpdate || 'Starter...'"></span>
        </div>
    </div>

    <!-- New Bill Modal -->
    <div x-show="showModal" 
         x-cloak
         @click.self="showModal = false"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-gradient-to-br from-purple-600 to-indigo-700 p-6 rounded-lg shadow-xl max-w-md w-full mx-4"
             @click.stop>
            <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                <span>💳</span>
                Nytt Forfall
            </h3>
            
            <div class="space-y-3">
                <div>
                    <label class="block text-sm mb-1">Navn</label>
                    <input 
                        type="text" 
                        x-model="newBill.name"
                        placeholder="Netflix, Strøm, Husleie..."
                        class="w-full px-3 py-2 bg-white bg-opacity-20 border border-white border-opacity-30 rounded text-white placeholder-white placeholder-opacity-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
                    />
                </div>
                
                <div>
                    <label class="block text-sm mb-1">Beløp (NOK)</label>
                    <input 
                        type="number" 
                        x-model="newBill.amount"
                        placeholder="149.00"
                        step="0.01"
                        min="0"
                        class="w-full px-3 py-2 bg-white bg-opacity-20 border border-white border-opacity-30 rounded text-white placeholder-white placeholder-opacity-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
                    />
                </div>
                
                <div>
                    <label class="block text-sm mb-1">Forfallsdag (1-31)</label>
                    <input 
                        type="number" 
                        x-model="newBill.due_day"
                        placeholder="15"
                        min="1"
                        max="31"
                        class="w-full px-3 py-2 bg-white bg-opacity-20 border border-white border-opacity-30 rounded text-white placeholder-white placeholder-opacity-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
                    />
                </div>
            </div>
            
            <div class="flex gap-2 mt-4">
                <button 
                    @click="createBill()"
                    class="flex-1 py-2 bg-white text-purple-700 font-semibold rounded hover:bg-opacity-90 transition-all"
                >
                    Lagre
                </button>
                <button 
                    @click="showModal = false; newBill = { name: '', amount: '', due_day: '' }"
                    class="flex-1 py-2 bg-white bg-opacity-20 rounded hover:bg-opacity-30 transition-all"
                >
                    Avbryt
                </button>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading && !data" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
        <p class="text-xs text-white text-opacity-70 mt-2">Laster...</p>
    </div>
</div>
