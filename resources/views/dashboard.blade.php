<x-app-layout>
    <div class="py-2 dashboard-scope" x-data="dashboardManager()">
        <div class="w-full px-2 sm:px-3 lg:px-4">
            
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- User's Widgets -->
            @php
                $userWidgets = Auth::user()->visibleWidgets()->get();
            @endphp

            @if($userWidgets->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Ingen widgets ennå</h3>
                    <p class="mt-1 text-sm text-gray-500">Kom i gang ved å legge til din første widget.</p>
                    <div class="mt-6">
                        <button @click="showPicker = true"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            + Legg til widget
                        </button>
                    </div>
                </div>
            @else
                <div id="widgets-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-2">
                    @foreach($userWidgets as $userWidget)
                        @php
                            $widget = $userWidget->widget;
                            $data = $userWidget->getData();
                            $viewName = str_replace('.', '-', $widget->key);
                            // Make CPU Cores widget compact (half width on larger screens)
                            $isCompact = in_array($widget->key, ['system.cpu-cores']);
                            $widgetSizeClass = $isCompact ? 'lg:col-span-1 xl:col-span-1' : '';
                        @endphp
                        
                    <div class="widget-item col-span-1 max-w-full h-full overflow-hidden shadow-sm sm:rounded-lg relative group flex flex-col {{ $widgetSizeClass }}"
                             data-widget-id="{{ $userWidget->id }}"
                             data-position="{{ $userWidget->position }}"
                        data-compact="{{ $isCompact ? 'true' : 'false' }}"
                        draggable="true">
                            
                            <!-- Widget Actions -->
                            <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none">
                                <button @click="openSettings({{ $userWidget->id }}, '{{ $widget->name }}', '{{ $widget->key }}', {{ $userWidget->refresh_interval ?? 'null' }}, {{ json_encode($userWidget->settings ?? []) }})"
                                        class="p-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs shadow-lg pointer-events-auto"
                                        title="Innstillinger">
                                    ⚙️
                                </button>
                                <form method="POST" action="{{ route('user-widgets.destroy', $userWidget) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            onclick="return confirm('Er du sikker på at du vil fjerne denne widgeten?')"
                                            class="p-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs shadow-lg pointer-events-auto"
                                            title="Fjern">
                                        ✕
                                    </button>
                                </form>
                            </div>

                            <!-- Move buttons (Arrow keys) -->
                            <div class="absolute top-2 left-2 opacity-0 group-hover:opacity-100 transition-opacity z-50 flex gap-1 pointer-events-none">
                                <button onclick="moveWidget({{ $userWidget->id }}, 'up')"
                                        class="p-1 bg-gray-700 text-white rounded hover:bg-gray-800 text-xs shadow-lg w-6 h-6 flex items-center justify-center pointer-events-auto"
                                        title="Flytt opp">
                                    ↑
                                </button>
                                <button onclick="moveWidget({{ $userWidget->id }}, 'down')"
                                        class="p-1 bg-gray-700 text-white rounded hover:bg-gray-800 text-xs shadow-lg w-6 h-6 flex items-center justify-center pointer-events-auto"
                                        title="Flytt ned">
                                    ↓
                                </button>
                                <button onclick="moveWidget({{ $userWidget->id }}, 'left')"
                                        class="p-1 bg-gray-700 text-white rounded hover:bg-gray-800 text-xs shadow-lg w-6 h-6 flex items-center justify-center pointer-events-auto"
                                        title="Flytt venstre">
                                    ←
                                </button>
                                <button onclick="moveWidget({{ $userWidget->id }}, 'right')"
                                        class="p-1 bg-gray-700 text-white rounded hover:bg-gray-800 text-xs shadow-lg w-6 h-6 flex items-center justify-center pointer-events-auto"
                                        title="Flytt høyre">
                                    →
                                </button>
                            </div>

                            <!-- Widget Content -->
                            @includeIf("widgets.{$viewName}", [
                                'data' => $data, 
                                'widget' => $widget,
                                'userWidget' => $userWidget
                            ])
                        </div>
                    @endforeach

                    <!-- Full-width Dashboard row inside the grid (force new row) -->
                    <div class="col-span-1 md:col-span-2 lg:col-span-3 xl:col-span-4 2xl:col-span-5" style="grid-column: 1 / -1;">
                        <div class="mt-2 bg-white shadow-sm rounded-lg p-4 w-full">
                            <div class="flex justify-between items-center">
                                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                                    Dashboard
                                </h2>
                                <button @click="showPicker = true"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
                                    + Legg til widget
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Footer with user info (inside grid, full width) -->
                    <div class="col-span-1 md:col-span-2 lg:col-span-3 xl:col-span-4 2xl:col-span-5" style="grid-column: 1 / -1;">
                        <div class="mt-2 text-center text-xs text-gray-500 space-y-1 pb-4">
                            <p>Logget inn som {{ Auth::user()->name }} ({{ Auth::user()->email }})</p>
                            @if(Auth::viaRemember())
                                <p>⏱️ Sesjon utløper om {{ config('dashboard.remember_days', 30) }} dager</p>
                            @else
                                <p>⏱️ Sesjon utløper om {{ config('session.lifetime', 120) }} minutter</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

        </div>

        <!-- Widget Picker Modal -->
        <div x-show="showPicker" 
             @open-widget-picker.window="showPicker = true"
             @click.self="showPicker = false"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
             style="display: none;">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Legg til widget</h3>
                        <button @click="showPicker = false" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div x-show="loadingWidgets" class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                        <p class="mt-2 text-sm text-gray-500">Laster tilgjengelige widgets...</p>
                    </div>

                    <div x-show="!loadingWidgets">
                        <template x-if="availableWidgets.length === 0">
                            <div class="text-center py-8 text-gray-500">
                                <p>Ingen flere widgets tilgjengelig.</p>
                                <p class="text-sm mt-2">Du har allerede lagt til alle aktive widgets.</p>
                            </div>
                        </template>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <template x-for="widget in availableWidgets" :key="widget.id">
                                <div class="border rounded-lg p-4 hover:border-indigo-500 cursor-pointer transition-colors"
                                     @click="addWidget(widget.id)">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900" x-text="widget.name"></h4>
                                            <p class="text-sm text-gray-500 mt-1" x-text="widget.description"></p>
                                            <span class="inline-block mt-2 px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800"
                                                  x-text="widget.category"></span>
                                        </div>
                                        <button class="ml-2 px-3 py-1 bg-indigo-600 text-white rounded text-sm hover:bg-indigo-700">
                                            + Legg til
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Modal -->
        <div x-show="showSettings" 
             @click.self="showSettings = false"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
             style="display: none;">
            <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Widget-innstillinger</h3>
                        <button @click="showSettings = false" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Widget</label>
                            <div class="text-lg font-semibold text-gray-900" x-text="settingsWidgetName"></div>
                        </div>

                        <div>
                            <label for="refresh-interval" class="block text-sm font-medium text-gray-700 mb-2">
                                Oppdateringsinterval (sekunder)
                            </label>
                            <input type="number" 
                                   id="refresh-interval"
                                   x-model="settingsRefreshInterval"
                                   min="10" 
                                   max="3600"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="text-xs text-gray-500 mt-1">La stå tom for å bruke standard intervall</p>
                        </div>

                        <!-- Website Uptime Monitoring Settings -->
                        <div x-show="settingsWidgetKey === 'monitoring.uptime'" class="space-y-4">
                            <h4 class="font-semibold text-gray-900 border-b pb-2">Overvåknings-konfigurasjon</h4>
                            
                            <!-- Websites List -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nettsider å overvåke
                                </label>
                                
                                <div class="space-y-2 mb-2">
                                    <template x-for="(site, index) in uptimeWebsites" :key="index">
                                        <div class="flex gap-2 items-start bg-gray-50 p-2 rounded">
                                            <div class="flex-1 space-y-1">
                                                <input type="text" 
                                                       x-model="site.name"
                                                       placeholder="Navn (f.eks. Smartesider)"
                                                       class="w-full border-gray-300 rounded text-sm">
                                                <input type="text" 
                                                       x-model="site.url"
                                                       placeholder="https://smartesider.no"
                                                       class="w-full border-gray-300 rounded text-sm">
                                            </div>
                                            <button @click="uptimeWebsites.splice(index, 1)" 
                                                    class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-xs mt-1">
                                                ✕
                                            </button>
                                        </div>
                                    </template>
                                    
                                    <template x-if="uptimeWebsites.length === 0">
                                        <p class="text-sm text-gray-500 italic">Ingen nettsider lagt til ennå</p>
                                    </template>
                                </div>
                                
                                <button @click="uptimeWebsites.push({ name: '', url: '' })" 
                                        class="w-full px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">
                                    + Legg til nettside
                                </button>
                            </div>

                            <div>
                                <label for="uptime-check-interval" class="block text-sm font-medium text-gray-700 mb-1">
                                    Sjekk-intervall
                                </label>
                                <select id="uptime-check-interval" 
                                        x-model="uptimeCheckInterval"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="30">Hver 30. sekund</option>
                                    <option value="60">Hvert minutt</option>
                                    <option value="300">Hver 5. minutt</option>
                                    <option value="600">Hver 10. minutt</option>
                                </select>
                            </div>

                            <div>
                                <label for="uptime-timeout" class="block text-sm font-medium text-gray-700 mb-1">
                                    Timeout (sekunder)
                                </label>
                                <input type="number" 
                                       id="uptime-timeout"
                                       x-model="uptimeTimeout"
                                       min="1"
                                       max="30"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <p class="text-xs text-gray-500 mt-1">Hvor lenge skal vi vente på svar (1-30 sekunder)</p>
                            </div>
                        </div>

                        <!-- GitHub Settings -->
                        <div x-show="settingsWidgetKey === 'dev.github'" class="space-y-4">
                            <h4 class="font-semibold text-gray-900 border-b pb-2">GitHub Konfigurasjon</h4>
                            
                            <div>
                                <label for="github-username" class="block text-sm font-medium text-gray-700 mb-1">
                                    GitHub Brukernavn *
                                </label>
                                <input type="text" 
                                       id="github-username"
                                       x-model="githubUsername"
                                       placeholder="octocat"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <p class="text-xs text-gray-500 mt-1">Ditt GitHub brukernavn</p>
                            </div>

                            <div>
                                <label for="github-token" class="block text-sm font-medium text-gray-700 mb-1">
                                    Personal Access Token *
                                </label>
                                <input type="password" 
                                       id="github-token"
                                       x-model="githubToken"
                                       placeholder="ghp_xxxxxxxxxxxxxxxxxxxx"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-mono">
                                <p class="text-xs text-gray-500 mt-1">
                                    Opprett på: <a href="https://github.com/settings/tokens" target="_blank" class="text-indigo-600 hover:underline">Settings → Developer settings → Personal access tokens</a>
                                </p>
                            </div>

                            <div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" 
                                           x-model="githubShowPrivate"
                                           class="rounded border-gray-300 text-indigo-600">
                                    <span class="text-sm font-medium text-gray-700">Vis private repositories</span>
                                </label>
                            </div>
                        </div>

                        <!-- RSS Feed Selection (only for RSS widgets) -->
                        <div x-show="settingsWidgetKey === 'news.rss'" class="space-y-4">
                            <h4 class="font-semibold text-gray-900 border-b pb-2">RSS Konfigurasjon</h4>
                            
                            <!-- Max items -->
                            <div>
                                <label for="rss-max-items" class="block text-sm font-medium text-gray-700 mb-1">
                                    Antall artikler å vise
                                </label>
                                <input type="number" 
                                       id="rss-max-items"
                                       x-model="rssMaxItems"
                                       min="1" 
                                       max="50"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <p class="text-xs text-gray-500 mt-1">Standard: 10 artikler</p>
                            </div>

                            <!-- Display mode -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Visningsm</label>
                                <select x-model="rssDisplayMode" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="mixed">Bland alle kilder (nyeste først)</option>
                                    <option value="grouped">Grupper per kilde</option>
                                    <option value="latest_per_source">Siste fra hver kilde</option>
                                </select>
                            </div>

                            <!-- Show images -->
                            <div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" 
                                           x-model="rssShowImages"
                                           class="rounded border-gray-300 text-indigo-600">
                                    <span class="text-sm font-medium text-gray-700">Vis bilder fra artikler (hvis tilgjengelig)</span>
                                </label>
                            </div>

                            <!-- Show descriptions -->
                            <div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" 
                                           x-model="rssShowDescriptions"
                                           class="rounded border-gray-300 text-indigo-600">
                                    <span class="text-sm font-medium text-gray-700">Vis beskrivelser/sammendrag</span>
                                </label>
                            </div>

                            <!-- Show source -->
                            <div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" 
                                           x-model="rssShowSource"
                                           class="rounded border-gray-300 text-indigo-600">
                                    <span class="text-sm font-medium text-gray-700">Vis kilde ved hver artikkel</span>
                                </label>
                            </div>

                            <!-- Custom feed URL -->
                            <div>
                                <label for="rss-custom-url" class="block text-sm font-medium text-gray-700 mb-1">
                                    Egen RSS-feed URL (valgfritt)
                                </label>
                                <input type="url" 
                                       id="rss-custom-url"
                                       x-model="rssCustomUrl"
                                       placeholder="https://smartesider.no/feed"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <p class="text-xs text-gray-500 mt-1">Legg til din egen RSS-feed for mer personlig innhold</p>
                            </div>

                            <!-- Feed selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Velg nyhetskilder
                                </label>
                                
                                <!-- Category filter buttons -->
                                <div class="flex flex-wrap gap-1 mb-2">
                                    <button @click="rssCategoryFilter = null" 
                                            :class="rssCategoryFilter === null ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'"
                                            class="px-2 py-1 text-xs rounded hover:bg-indigo-500 hover:text-white">
                                        Alle
                                    </button>
                                    @php
                                        $categories = \App\Models\RssFeed::active()->pluck('category')->unique()->sort();
                                    @endphp
                                    @foreach($categories as $category)
                                        <button @click="rssCategoryFilter = '{{ $category }}'" 
                                                :class="rssCategoryFilter === '{{ $category }}' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'"
                                                class="px-2 py-1 text-xs rounded hover:bg-indigo-500 hover:text-white">
                                            {{ $category }}
                                        </button>
                                    @endforeach
                                </div>

                                <!-- Feed list -->
                                <div class="space-y-1 max-h-60 overflow-y-auto border border-gray-200 rounded-md p-2">
                                    @foreach(\App\Models\RssFeed::active()->orderBy('category')->orderBy('name')->get() as $feed)
                                        <label x-show="rssCategoryFilter === null || rssCategoryFilter === '{{ $feed->category }}'"
                                               class="flex items-start gap-2 p-1.5 hover:bg-gray-50 rounded cursor-pointer">
                                            <input type="checkbox" 
                                                   value="{{ $feed->id }}"
                                                   x-model="selectedRssFeeds"
                                                   class="mt-1 rounded border-gray-300 text-indigo-600">
                                            <div class="flex-1">
                                                <div class="font-medium text-xs">{{ $feed->name }}</div>
                                                @if($feed->category)
                                                    <div class="text-xs text-gray-500">{{ $feed->category }}</div>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    <span x-show="selectedRssFeeds.length > 0" x-text="selectedRssFeeds.length + ' kilder valgt'"></span>
                                    <span x-show="selectedRssFeeds.length === 0">Ingen valgt - viser alle aktive kilder</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 mt-6">
                            <button @click="showSettings = false" 
                                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Avbryt
                            </button>
                            <button @click="saveSettings" 
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Lagre
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function dashboardManager() {
            return {
                showPicker: false,
                showSettings: false,
                availableWidgets: [],
                loadingWidgets: false,
                settingsUserWidgetId: null,
                settingsWidgetName: '',
                settingsWidgetKey: '',
                settingsRefreshInterval: null,
                selectedRssFeeds: [],
                rssMaxItems: 10,
                rssDisplayMode: 'mixed',
                rssShowImages: false,
                rssShowDescriptions: true,
                rssShowSource: true,
                rssCustomUrl: '',
                rssCategoryFilter: null,
                // Uptime monitoring settings
                uptimeWebsites: [
                    { name: 'Smartesider', url: 'https://smartesider.no' }
                ],
                uptimeCheckInterval: '60',
                uptimeTimeout: 5,
                // GitHub settings
                githubUsername: '',
                githubToken: '',
                githubShowPrivate: false,
                sortable: null,

                init() {
                    this.$watch('showPicker', (value) => {
                        if (value) {
                            this.loadAvailableWidgets();
                        }
                    });

                    // Initialize drag-and-drop
                    this.initDragDrop();
                },

                initDragDrop() {
                    const grid = document.getElementById('widgets-grid');
                    if (!grid) return;

                    // Mark grid as dropzone
                    grid.addEventListener('dragover', (e) => {
                        e.preventDefault();
                        const afterElement = this.getDragAfterElement(grid, e.clientY, e.clientX);
                        const dragging = grid.querySelector('.widget-item.dragging');
                        if (!dragging) return;
                        if (afterElement == null) {
                            grid.appendChild(dragging);
                        } else {
                            grid.insertBefore(dragging, afterElement);
                        }
                    });

                    grid.addEventListener('drop', (e) => {
                        e.preventDefault();
                        this.updatePositions();
                    });

                    // Items
                    grid.querySelectorAll('.widget-item').forEach((item) => {
                        item.addEventListener('dragstart', (e) => {
                            e.dataTransfer.effectAllowed = 'move';
                            item.classList.add('dragging', 'opacity-50');
                        });
                        item.addEventListener('dragend', () => {
                            item.classList.remove('dragging', 'opacity-50');
                        });
                    });
                },

                getDragAfterElement(container, y, x) {
                    const elements = [...container.querySelectorAll('.widget-item:not(.dragging)')];
                    if (elements.length === 0) return null;
                    // Choose the element with the smallest positive distance from pointer to its center
                    let best = { dist: Infinity, element: null };
                    for (const el of elements) {
                        const r = el.getBoundingClientRect();
                        const cx = r.left + r.width / 2;
                        const cy = r.top + r.height / 2;
                        const dy = y - cy;
                        const dx = x - cx;
                        const dist = Math.hypot(dx, dy);
                        // Only consider elements that are after the pointer (above/left gets insert before)
                        if (y < cy || (Math.abs(y - cy) < r.height / 2 && x < cx)) {
                            if (dist < best.dist) best = { dist, element: el };
                        }
                    }
                    return best.element;
                },

                async updatePositions() {
                    const grid = document.getElementById('widgets-grid');
                    if (!grid) return;

                    const positions = [];
                    const items = grid.querySelectorAll('.widget-item');
                    
                    items.forEach((item, index) => {
                        positions.push({
                            id: parseInt(item.dataset.widgetId),
                            position: index
                        });
                    });

                    try {
                        const response = await fetch('{{ route('user-widgets.positions') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ positions })
                        });

                        if (response.ok) {
                            console.log('Positions updated');
                        }
                    } catch (error) {
                        console.error('Error updating positions:', error);
                    }
                },

                openSettings(userWidgetId, widgetName, widgetKey, refreshInterval, settings) {
                    this.settingsUserWidgetId = userWidgetId;
                    this.settingsWidgetName = widgetName;
                    this.settingsWidgetKey = widgetKey;
                    this.settingsRefreshInterval = refreshInterval;
                    
                    // Load RSS settings if this is an RSS widget
                    if (widgetKey === 'news.rss' && settings) {
                        this.selectedRssFeeds = settings.feed_ids || [];
                        this.rssMaxItems = settings.max_items || 10;
                        this.rssDisplayMode = settings.display_mode || 'mixed';
                        this.rssShowImages = settings.show_images || false;
                        this.rssShowDescriptions = settings.show_descriptions !== false; // Default true
                        this.rssShowSource = settings.show_source !== false; // Default true
                        this.rssCustomUrl = settings.custom_url || '';
                    } else {
                        // Reset RSS settings
                        this.selectedRssFeeds = [];
                        this.rssMaxItems = 10;
                        this.rssDisplayMode = 'mixed';
                        this.rssShowImages = false;
                        this.rssShowDescriptions = true;
                        this.rssShowSource = true;
                        this.rssCustomUrl = '';
                    }
                    
                    // Load Uptime monitoring settings
                    if (widgetKey === 'monitoring.uptime' && settings) {
                        this.uptimeWebsites = settings.websites || [{ name: 'Smartesider', url: 'https://smartesider.no' }];
                        this.uptimeCheckInterval = settings.check_interval || '60';
                        this.uptimeTimeout = settings.timeout || 5;
                    } else if (widgetKey !== 'monitoring.uptime') {
                        this.uptimeWebsites = [{ name: 'Smartesider', url: 'https://smartesider.no' }];
                        this.uptimeCheckInterval = '60';
                        this.uptimeTimeout = 5;
                    }
                    
                    // Load GitHub settings
                    if (widgetKey === 'dev.github' && settings) {
                        this.githubUsername = settings.username || '';
                        this.githubToken = settings.token || '';
                        this.githubShowPrivate = settings.show_private || false;
                    } else if (widgetKey !== 'dev.github') {
                        this.githubUsername = '';
                        this.githubToken = '';
                        this.githubShowPrivate = false;
                    }
                    
                    this.showSettings = true;
                },

                async saveSettings() {
                    try {
                        const settings = {};
                        
                        // Add RSS configuration if this is an RSS widget
                        if (this.settingsWidgetKey === 'news.rss') {
                            if (this.selectedRssFeeds.length > 0) {
                                settings.feed_ids = this.selectedRssFeeds.map(id => parseInt(id));
                            }
                            settings.max_items = parseInt(this.rssMaxItems) || 10;
                            settings.display_mode = this.rssDisplayMode;
                            settings.show_images = this.rssShowImages;
                            settings.show_descriptions = this.rssShowDescriptions;
                            settings.show_source = this.rssShowSource;
                            if (this.rssCustomUrl) {
                                settings.custom_url = this.rssCustomUrl;
                            }
                        }
                        
                        // Add Uptime monitoring configuration
                        if (this.settingsWidgetKey === 'monitoring.uptime') {
                            // Filter out empty websites
                            const websites = this.uptimeWebsites.filter(site => site.url && site.url.trim() !== '');
                            settings.websites = websites.map(site => ({
                                name: site.name || new URL(site.url).hostname,
                                url: site.url
                            }));
                            settings.check_interval = this.uptimeCheckInterval || '60';
                            settings.timeout = parseInt(this.uptimeTimeout) || 5;
                        }
                        
                        // Add GitHub configuration
                        if (this.settingsWidgetKey === 'dev.github') {
                            settings.username = this.githubUsername || '';
                            settings.token = this.githubToken || '';
                            settings.show_private = this.githubShowPrivate || false;
                        }
                        
                        const payload = {
                            refresh_interval: this.settingsRefreshInterval || null,
                            settings: settings
                        };
                        
                        console.log('Saving settings for widget:', this.settingsUserWidgetId);
                        console.log('Payload:', JSON.stringify(payload, null, 2));
                        
                        const response = await fetch(`/my-widgets/${this.settingsUserWidgetId}`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(payload)
                        });

                        console.log('Response status:', response.status);
                        console.log('Response headers:', Object.fromEntries([...response.headers]));
                        
                        const responseText = await response.text();
                        console.log('Response body:', responseText);
                        
                        if (!response.ok) {
                            let errorMessage = response.statusText;
                            try {
                                const errorData = JSON.parse(responseText);
                                console.error('Error response:', errorData);
                                errorMessage = errorData.message || errorMessage;
                            } catch (e) {
                                console.error('Could not parse error response as JSON');
                            }
                            alert('Kunne ikke lagre: ' + errorMessage);
                            return;
                        }

                        let result;
                        try {
                            result = JSON.parse(responseText);
                            console.log('Success:', result);
                        } catch (e) {
                            console.error('Could not parse success response as JSON');
                            console.log('Response was:', responseText);
                        }
                        
                        this.showSettings = false;
                        window.location.reload();
                    } catch (error) {
                        console.error('Error saving settings:', error);
                        alert('Kunne ikke lagre innstillinger: ' + error.message);
                    }
                },

                async loadAvailableWidgets() {
                    this.loadingWidgets = true;
                    try {
                        const response = await fetch('{{ route('user-widgets.available') }}', {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });
                        
                        if (!response.ok) {
                            console.error('Failed to load available widgets:', response.status, response.statusText);
                            this.availableWidgets = [];
                            return;
                        }
                        
                        this.availableWidgets = await response.json();
                        console.log('Available widgets loaded:', this.availableWidgets.length);
                    } catch (error) {
                        console.error('Error loading available widgets:', error);
                        this.availableWidgets = [];
                    } finally {
                        this.loadingWidgets = false;
                    }
                },

                async addWidget(widgetId) {
                    try {
                        const response = await fetch('{{ route('user-widgets.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ widget_id: widgetId })
                        });

                        if (response.ok) {
                            window.location.reload();
                        }
                    } catch (error) {
                        console.error('Error adding widget:', error);
                        alert('Kunne ikke legge til widget. Prøv igjen.');
                    }
                }
            }
        }

        // Global function to move widgets with arrow buttons
        async function moveWidget(widgetId, direction) {
            const grid = document.getElementById('widgets-grid');
            if (!grid) return;

            const items = Array.from(grid.querySelectorAll('.widget-item'));
            const currentIndex = items.findIndex(item => parseInt(item.dataset.widgetId) === widgetId);
            
            if (currentIndex === -1) return;

            let targetIndex = currentIndex;
            // Robust column count: count items on first row using offsetTop
            const getGridCols = (container) => {
                const children = Array.from(container.querySelectorAll('.widget-item'));
                if (children.length <= 1) return 1;
                const firstTop = children[0].offsetTop;
                let count = 0;
                for (const el of children) {
                    if (el.offsetTop !== firstTop) break;
                    count++;
                }
                return Math.max(1, count);
            };
            const cols = getGridCols(grid);

            switch(direction) {
                case 'up':
                    targetIndex = Math.max(0, currentIndex - cols);
                    break;
                case 'down':
                    targetIndex = Math.min(items.length - 1, currentIndex + cols);
                    break;
                case 'left':
                    targetIndex = Math.max(0, currentIndex - 1);
                    break;
                case 'right':
                    targetIndex = Math.min(items.length - 1, currentIndex + 1);
                    break;
            }

            if (targetIndex === currentIndex) return;

            // Swap elements in DOM
            const currentElement = items[currentIndex];
            const targetElement = items[targetIndex];

            if (targetIndex < currentIndex) {
                grid.insertBefore(currentElement, targetElement);
            } else {
                grid.insertBefore(currentElement, targetElement.nextSibling);
            }

            // Update positions on server
            const positions = [];
            grid.querySelectorAll('.widget-item').forEach((item, index) => {
                positions.push({
                    id: parseInt(item.dataset.widgetId),
                    position: index
                });
            });

            try {
                const response = await fetch('{{ route('user-widgets.positions') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ positions })
                });

                if (response.ok) {
                    console.log('Widget moved:', direction);
                } else {
                    console.error('Failed to save positions');
                    location.reload(); // Reload to revert
                }
            } catch (error) {
                console.error('Error moving widget:', error);
                location.reload();
            }
        }
    </script>
</x-app-layout>