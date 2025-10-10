<div class="widget-card bg-gradient-to-br from-purple-600 to-indigo-700 text-white p-4 rounded-lg shadow-lg h-full flex flex-col"
     x-data="toolsQuicklinks()">
    
    <!-- Widget Title & Actions -->
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-semibold flex items-center gap-2">
            <span class="text-2xl">🔗</span>
            Hurtiglenker
        </h3>
        <div class="flex items-center gap-2">
            <!-- Add Button -->
            <button 
                @click="showAddForm = !showAddForm"
                class="w-8 h-8 rounded-full bg-white bg-opacity-20 hover:bg-opacity-30 flex items-center justify-center transition-all"
                title="Legg til lenke"
            >
                <span class="text-xl font-bold" x-text="showAddForm ? '×' : '+'"></span>
            </button>
            
            <!-- Edit/Delete Mode Toggle -->
            <button 
                @click="toggleEditMode()"
                class="w-8 h-8 rounded-full bg-white bg-opacity-20 hover:bg-opacity-30 flex items-center justify-center transition-all"
                :class="editMode ? 'bg-red-500 bg-opacity-50' : ''"
                title="Redigeringsmodus"
            >
                <span class="text-lg font-bold" x-text="editMode ? '✓' : '−'"></span>
            </button>
        </div>
    </div>

    <!-- Error banner -->
    <template x-if="lastError">
        <div class="mb-3 text-xs bg-red-500 bg-opacity-20 border border-red-400 border-opacity-40 text-white px-3 py-2 rounded">
            <span x-text="lastError"></span>
        </div>
    </template>

    <!-- Add Form -->
    <div x-show="showAddForm" 
         x-transition
         class="mb-3 bg-white bg-opacity-10 rounded-lg p-3 backdrop-blur-sm">
        <div class="space-y-2">
            <input 
                type="text" 
                x-model="newTitle"
                placeholder="Tittel"
                class="w-full px-3 py-2 bg-white bg-opacity-20 backdrop-blur-sm border border-white border-opacity-30 rounded text-white placeholder-white placeholder-opacity-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 text-sm"
                @keyup.enter="addLink()"
            />
            <input 
                type="url" 
                x-model="newUrl"
                placeholder="https://example.com"
                class="w-full px-3 py-2 bg-white bg-opacity-20 backdrop-blur-sm border border-white border-opacity-30 rounded text-white placeholder-white placeholder-opacity-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 text-sm"
                @keyup.enter="addLink()"
            />
            <button 
                @click="addLink()"
                :disabled="saving || !newTitle || !newUrl"
                class="w-full px-3 py-2 rounded font-semibold transition-all text-sm"
                :class="saving || !newTitle || !newUrl ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-500'"
            >
                <span x-show="!saving">Legg til</span>
                <span x-show="saving">⏳ Lagrer...</span>
            </button>
        </div>
    </div>

    <!-- Delete Button (shown in edit mode) -->
    <div x-show="editMode && selectedIds.length > 0" 
         x-transition
         class="mb-3">
        <button 
            @click="deleteSelected()"
            :disabled="saving"
            class="w-full px-3 py-2 rounded font-semibold transition-all text-sm bg-red-600 hover:bg-red-500"
        >
            <span x-show="!saving">🗑️ Slett <span x-text="selectedIds.length"></span> valgte</span>
            <span x-show="saving">⏳ Sletter...</span>
        </button>
    </div>

    <!-- Links List -->
    <div class="widget-body space-y-2" style="scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.3) transparent;">
        <template x-if="links.length === 0">
            <div class="text-center text-white text-opacity-70 py-8 text-sm" x-show="!loadingLinks">
                <div class="text-4xl mb-2">🔗</div>
                <div>Ingen lenker lagt til ennå</div>
                <div class="text-xs mt-1">Klikk + for å legge til</div>
            </div>
        </template>
        <template x-if="loadingLinks">
            <div class="text-center text-white text-opacity-70 py-8 text-sm">
                Laster lenker...
            </div>
        </template>
        
        <template x-if="links.length > 0">
            <ul class="space-y-2">
                <template x-for="link in links" :key="link.id">
                    <li class="flex items-center gap-2">
                        <!-- Checkbox (edit mode) -->
                        <div x-show="editMode" class="flex-shrink-0">
                            <input 
                                type="checkbox"
                                :checked="selectedIds.includes(link.id)"
                                @change="toggleSelection(link.id)"
                                class="w-4 h-4 rounded border-white border-opacity-30"
                            />
                        </div>
                        
                        <!-- Link -->
                        <a 
                            :href="link.url" 
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex-1 bg-white bg-opacity-10 hover:bg-opacity-20 rounded px-3 py-2 transition-all flex items-center justify-between group"
                            :class="editMode ? 'pointer-events-none opacity-50' : ''"
                        >
                            <span class="text-sm font-medium truncate" x-text="link.title"></span>
                            <span class="text-xs opacity-0 group-hover:opacity-100 transition-opacity">↗</span>
                        </a>
                    </li>
                </template>
            </ul>
        </template>
    </div>

    <!-- Status Footer -->
    <div class="flex items-center justify-between text-xs text-white text-opacity-70 pt-2 mt-2 border-t border-white border-opacity-20">
        <div class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-green-400"></span>
            <span x-text="links.length + ' lenker'"></span>
        </div>
        <div>Hurtiglenker</div>
    </div>
</div>
