<div 
    class="widget h-full"
    x-data="widgetData('{{ $widget->key ?? 'inspiration.quote' }}')"
>
    <div class="widget-header">
        <h3 class="widget-title">💭 Quote of The Day</h3>
        <button 
            @click="fetchData()" 
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
                <button @click="fetchData()" class="text-sm text-blue-400 hover:text-blue-300">
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
                                'text-base': data && data.quote && data.quote.length > 150,
                                'text-lg': data && data.quote && data.quote.length <= 150
                            }"
                            x-text="data ? data.quote : ''"
                        ></p>
                    </blockquote>
                    <div class="absolute bottom-0 right-4 text-6xl text-gray-700 opacity-20">"</div>
                </div>

                <!-- Author -->
                <div class="flex items-center justify-end space-x-2 px-4">
                    <div class="h-px flex-1 bg-gradient-to-r from-transparent to-gray-700"></div>
                    <cite class="text-sm font-medium not-italic text-gray-300" x-text="data ? ('— ' + data.author) : ''"></cite>
                </div>

                <!-- Source badge -->
                <div class="flex items-center justify-between px-2 text-xs text-gray-500">
                    <div class="flex items-center space-x-2">
                        <template x-if="data && data.success">
                            <span class="px-2 py-1 bg-green-900 bg-opacity-30 text-green-400 rounded">
                                ✓ Live
                            </span>
                        </template>
                        <template x-if="data && !data.success">
                            <span class="px-2 py-1 bg-yellow-900 bg-opacity-30 text-yellow-400 rounded">
                                📦 Cached
                            </span>
                        </template>
                        <span x-text="data && data.source ? ('Source: ' + data.source) : ''"></span>
                    </div>
                    <div class="text-gray-600">
                        Daily quote
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
