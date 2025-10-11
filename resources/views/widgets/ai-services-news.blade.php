<div 
    class="widget h-full"
    x-data="aiServicesNews()"
    x-init="init()"
>
    <div class="widget-header">
        <h3 class="widget-title">🤖 AI Services News</h3>
        <div class="flex items-center space-x-2">
            <button 
                @click="refresh()" 
                class="widget-action"
                :disabled="loading"
                title="Refresh news"
            >
                <svg class="w-4 h-4" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>
        </div>
    </div>

    <div class="widget-body">
        <!-- Filter Tabs -->
        <div class="flex items-center space-x-2 mb-3 pb-2 border-b border-gray-800 overflow-x-auto">
            <button 
                @click="activeFilter = 'all'" 
                :class="activeFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white'"
                class="px-3 py-1 rounded text-xs whitespace-nowrap transition-colors"
            >
                All
            </button>
            <button 
                @click="activeFilter = 'openai'" 
                :class="activeFilter === 'openai' ? 'bg-blue-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white'"
                class="px-3 py-1 rounded text-xs whitespace-nowrap transition-colors"
            >
                🤖 OpenAI
            </button>
            <button 
                @click="activeFilter = 'claude'" 
                :class="activeFilter === 'claude' ? 'bg-blue-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white'"
                class="px-3 py-1 rounded text-xs whitespace-nowrap transition-colors"
            >
                🧠 Claude
            </button>
            <button 
                @click="activeFilter = 'copilot'" 
                :class="activeFilter === 'copilot' ? 'bg-blue-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white'"
                class="px-3 py-1 rounded text-xs whitespace-nowrap transition-colors"
            >
                🚀 Copilot
            </button>
            <button 
                @click="activeFilter = 'vscode'" 
                :class="activeFilter === 'vscode' ? 'bg-blue-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white'"
                class="px-3 py-1 rounded text-xs whitespace-nowrap transition-colors"
            >
                📝 VS Code
            </button>
        </div>

        <template x-if="loading">
            <div class="flex items-center justify-center py-8">
                <div class="animate-pulse text-gray-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </div>
            </div>
        </template>

        <template x-if="!loading && error">
            <div class="text-center py-4">
                <div class="text-red-400 mb-2">⚠️ Could not fetch AI news</div>
                <button @click="refresh()" class="text-sm text-blue-400 hover:text-blue-300">
                    Try again
                </button>
            </div>
        </template>

        <template x-if="!loading && !error">
            <!-- News List -->
            <div class="space-y-2 max-h-96 overflow-y-auto custom-scrollbar">
                <template x-for="item in filteredNews" :key="item.url">
                    <a 
                        :href="item.url" 
                        target="_blank"
                        class="block p-3 bg-gray-800 bg-opacity-30 hover:bg-opacity-50 rounded transition-all border border-gray-800 hover:border-gray-700"
                    >
                        <div class="flex items-start space-x-3">
                            <!-- Icon -->
                            <div class="text-2xl flex-shrink-0" x-text="item.icon"></div>

                            <div class="flex-1 min-w-0">
                                <!-- Title -->
                                <h4 class="text-sm font-medium text-white mb-1 line-clamp-2" x-text="item.title"></h4>

                                <!-- Description -->
                                <p class="text-xs text-gray-400 mb-2 line-clamp-2" x-text="item.description"></p>

                                <!-- Meta -->
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center space-x-2">
                                        <span 
                                            class="px-2 py-0.5 rounded"
                                            :class="{
                                                'bg-green-900 bg-opacity-30 text-green-400': item.category === 'openai',
                                                'bg-purple-900 bg-opacity-30 text-purple-400': item.category === 'claude',
                                                'bg-blue-900 bg-opacity-30 text-blue-400': item.category === 'copilot',
                                                'bg-yellow-900 bg-opacity-30 text-yellow-400': item.category === 'vscode',
                                            }"
                                            x-text="item.source"
                                        ></span>
                                        
                                        <!-- New badge (within 7 days) -->
                                        <template x-if="isNew(item.date)">
                                            <span class="px-2 py-0.5 bg-red-900 bg-opacity-30 text-red-400 rounded animate-pulse">
                                                NEW
                                            </span>
                                        </template>
                                    </div>

                                    <span class="text-gray-500" x-text="formatDate(item.date)"></span>
                                </div>
                            </div>

                            <!-- External link icon -->
                            <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </div>
                    </a>
                </template>

                <template x-if="filteredNews.length === 0">
                    <div class="text-center py-8 text-gray-500">
                        <div class="mb-2">📭</div>
                        <div class="text-sm">No news found for this filter</div>
                    </div>
                </template>
            </div>

            <!-- Footer -->
            <div class="mt-3 pt-2 border-t border-gray-800 text-xs text-gray-500 flex items-center justify-between">
                <div>
                    Updated: <span x-text="formatDate(data.updated_at)"></span>
                </div>
                <div>
                    <span x-text="filteredNews.length"></span> news items
                </div>
            </div>
        </template>
    </div>
</div>

<script>
function aiServicesNews() {
    return {
        data: null,
        loading: true,
        error: false,
        activeFilter: 'all',

        init() {
            this.fetchData();
        },

        async fetchData() {
            this.loading = true;
            this.error = false;

            try {
                const response = await fetch('/api/widgets/ai-services-news');
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const result = await response.json();
                this.data = result;
            } catch (error) {
                console.error('Error fetching AI news:', error);
                this.error = true;
            } finally {
                this.loading = false;
            }
        },

        refresh() {
            this.fetchData();
        },

        get filteredNews() {
            if (!this.data || !this.data.news) return [];

            if (this.activeFilter === 'all') {
                return this.data.news;
            }

            return this.data.news.filter(item => item.category === this.activeFilter);
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

            if (diffDays === 0) return 'Today';
            if (diffDays === 1) return 'Yesterday';
            if (diffDays < 7) return `${diffDays} days ago`;
            if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;

            return date.toLocaleDateString('no-NO', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        },

        isNew(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

            return diffDays <= 7;
        }
    };
}
</script>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}
</style>
