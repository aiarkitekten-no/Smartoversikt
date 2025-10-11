<div 
    class="widget h-full"
    x-data="inspirationQuote()"
    x-init="init()"
>
    <div class="widget-header">
        <h3 class="widget-title">💭 Quote of The Day</h3>
        <button 
            @click="refresh()" 
            class="widget-action"
            :disabled="loading"
            title="Refresh quote"
        >
            <svg class="w-4 h-4" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
        </button>
    </div>

    <div class="widget-body">
        <template x-if="loading">
            <div class="flex items-center justify-center py-8">
                <div class="animate-pulse text-gray-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
            </div>
        </template>

        <template x-if="!loading && error">
            <div class="text-center py-4">
                <div class="text-red-400 mb-2">⚠️ Kunne ikke hente quote</div>
                <button @click="refresh()" class="text-sm text-blue-400 hover:text-blue-300">
                    Prøv igjen
                </button>
            </div>
        </template>

        <template x-if="!loading && !error && data">
            <div class="space-y-4">
                <!-- Quote -->
                <div class="relative">
                    <div class="absolute top-0 left-0 text-6xl text-gray-700 opacity-20">"</div>
                    <blockquote class="relative pl-8 pr-4 py-2">
                        <p 
                            class="text-lg leading-relaxed italic"
                            :class="{
                                'text-base': data.quote && data.quote.length > 150,
                                'text-lg': data.quote && data.quote.length <= 150
                            }"
                            x-text="data.quote"
                        ></p>
                    </blockquote>
                    <div class="absolute bottom-0 right-4 text-6xl text-gray-700 opacity-20">"</div>
                </div>

                <!-- Author -->
                <div class="flex items-center justify-end space-x-2 px-4">
                    <div class="h-px flex-1 bg-gradient-to-r from-transparent to-gray-700"></div>
                    <cite class="text-sm font-medium not-italic text-gray-300" x-text="'— ' + data.author"></cite>
                </div>

                <!-- Source badge -->
                <div class="flex items-center justify-between px-2 text-xs text-gray-500">
                    <div class="flex items-center space-x-2">
                        <template x-if="data.success">
                            <span class="px-2 py-1 bg-green-900 bg-opacity-30 text-green-400 rounded">
                                ✓ Live
                            </span>
                        </template>
                        <template x-if="!data.success">
                            <span class="px-2 py-1 bg-yellow-900 bg-opacity-30 text-yellow-400 rounded">
                                📦 Cached
                            </span>
                        </template>
                        <span x-text="'Source: ' + data.source"></span>
                    </div>
                    <div class="text-gray-600">
                        Daily quote
                    </div>
                </div>

                <!-- Share options (optional) -->
                <div class="flex items-center justify-center space-x-3 pt-2 border-t border-gray-800">
                    <button 
                        @click="shareQuote('twitter')" 
                        class="p-2 hover:bg-gray-800 rounded transition-colors"
                        title="Share on Twitter"
                    >
                        <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"></path>
                        </svg>
                    </button>
                    <button 
                        @click="copyQuote()" 
                        class="p-2 hover:bg-gray-800 rounded transition-colors"
                        title="Copy to clipboard"
                    >
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
function inspirationQuote() {
    return {
        data: null,
        loading: true,
        error: false,

        init() {
            this.fetchData();
        },

        async fetchData() {
            this.loading = true;
            this.error = false;

            try {
                const response = await fetch('/api/widgets/inspiration-quote');
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const result = await response.json();
                this.data = result;
            } catch (error) {
                console.error('Error fetching quote:', error);
                this.error = true;
            } finally {
                this.loading = false;
            }
        },

        refresh() {
            this.fetchData();
        },

        shareQuote(platform) {
            if (!this.data) return;

            const text = `"${this.data.quote}" — ${this.data.author}`;
            const url = window.location.origin;

            if (platform === 'twitter') {
                const twitterUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`;
                window.open(twitterUrl, '_blank', 'width=550,height=420');
            }
        },

        async copyQuote() {
            if (!this.data) return;

            const text = `"${this.data.quote}" — ${this.data.author}`;
            
            try {
                await navigator.clipboard.writeText(text);
                
                // Visual feedback
                const button = event.target.closest('button');
                const originalHTML = button.innerHTML;
                button.innerHTML = '<svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                }, 2000);
            } catch (error) {
                console.error('Failed to copy:', error);
            }
        }
    };
}
</script>
