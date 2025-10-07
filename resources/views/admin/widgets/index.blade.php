<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Widget-administrasjon') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="widgetAdmin()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
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

            <!-- Filters and Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex flex-wrap gap-4 items-center justify-between">
                        <!-- Filters -->
                        <div class="flex gap-4 flex-wrap">
                            <div>
                                <label class="text-sm text-gray-600">Kategori:</label>
                                <select onchange="window.location.search = updateUrlParam('category', this.value)" 
                                        class="ml-2 border-gray-300 rounded-md shadow-sm">
                                    <option value="all">Alle</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                                            {{ ucfirst($cat) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-sm text-gray-600">Status:</label>
                                <select onchange="window.location.search = updateUrlParam('status', this.value)" 
                                        class="ml-2 border-gray-300 rounded-md shadow-sm">
                                    <option value="all">Alle</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktive</option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inaktive</option>
                                </select>
                            </div>
                        </div>

                        <!-- Bulk Actions -->
                        <div x-show="selectedWidgets.length > 0" class="flex gap-2">
                            <span class="text-sm text-gray-600 self-center">
                                <span x-text="selectedWidgets.length"></span> valgt
                            </span>
                            <button @click="bulkAction('enable')" class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                Aktiver
                            </button>
                            <button @click="bulkAction('disable')" class="px-3 py-1 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700">
                                Deaktiver
                            </button>
                            <button @click="bulkAction('refresh')" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                                Oppdater
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Widgets Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" 
                                           @change="toggleAll($event.target.checked)"
                                           :checked="allSelected"
                                           class="rounded border-gray-300">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Rekkefølge
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Widget
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kategori
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sist oppdatert
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Handlinger
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($widgets as $widget)
                                <tr class="hover:bg-gray-50" :class="{ 'bg-blue-50': selectedWidgets.includes({{ $widget->id }}) }">
                                    <td class="px-3 py-4">
                                        <input type="checkbox" 
                                               :checked="selectedWidgets.includes({{ $widget->id }})"
                                               @change="toggleWidget({{ $widget->id }})"
                                               class="rounded border-gray-300">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <input type="number" 
                                               value="{{ $widget->order }}"
                                               @change="updateWidgetOrder({{ $widget->id }}, $event.target.value)"
                                               class="w-16 border-gray-300 rounded-md shadow-sm text-sm"
                                               min="0">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $widget->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $widget->key }}</div>
                                        @if($widget->description)
                                            <div class="text-xs text-gray-400 mt-1">{{ Str::limit($widget->description, 60) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ ucfirst($widget->category) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <form method="POST" action="{{ route('admin.widgets.toggle', $widget) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="focus:outline-none">
                                                @if($widget->is_active)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 cursor-pointer hover:bg-green-200">
                                                        ✓ Aktiv
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 cursor-pointer hover:bg-gray-200">
                                                        ○ Inaktiv
                                                    </span>
                                                @endif
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($widget->latestSnapshot)
                                            <div>{{ $widget->latestSnapshot->fresh_at->diffForHumans() }}</div>
                                            <div class="text-xs text-gray-400">{{ $widget->latestSnapshot->fresh_at->format('d.m.Y H:i') }}</div>
                                        @else
                                            <span class="text-gray-400">Aldri</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="editWidget({{ $widget->id }})" 
                                                class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            Rediger
                                        </button>
                                        <button @click="deleteWidget({{ $widget->id }}, '{{ $widget->name }}')" 
                                                class="text-red-600 hover:text-red-900">
                                            Slett
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        Ingen widgets funnet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Hidden form for bulk actions -->
        <form id="bulk-action-form" method="POST" action="{{ route('admin.widgets.bulk') }}" style="display: none;">
            @csrf
            <input type="hidden" name="action" x-model="bulkActionType">
            <template x-for="id in selectedWidgets" :key="id">
                <input type="hidden" name="widget_ids[]" :value="id">
            </template>
        </form>

        <!-- Hidden form for delete -->
        <form id="delete-form" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div>

    <script>
        function widgetAdmin() {
            return {
                selectedWidgets: [],
                bulkActionType: '',

                get allSelected() {
                    const widgetIds = @json($widgets->pluck('id'));
                    return widgetIds.length > 0 && widgetIds.every(id => this.selectedWidgets.includes(id));
                },

                toggleWidget(id) {
                    const index = this.selectedWidgets.indexOf(id);
                    if (index > -1) {
                        this.selectedWidgets.splice(index, 1);
                    } else {
                        this.selectedWidgets.push(id);
                    }
                },

                toggleAll(checked) {
                    if (checked) {
                        this.selectedWidgets = @json($widgets->pluck('id'));
                    } else {
                        this.selectedWidgets = [];
                    }
                },

                bulkAction(action) {
                    if (this.selectedWidgets.length === 0) {
                        alert('Velg minst én widget.');
                        return;
                    }

                    const confirmMsg = action === 'enable' ? 'aktivere' : (action === 'disable' ? 'deaktivere' : 'oppdatere');
                    if (confirm(`Er du sikker på at du vil ${confirmMsg} ${this.selectedWidgets.length} widget(s)?`)) {
                        this.bulkActionType = action;
                        this.$nextTick(() => {
                            document.getElementById('bulk-action-form').submit();
                        });
                    }
                },

                updateWidgetOrder(widgetId, newOrder) {
                    fetch('{{ route('admin.widgets.order') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            orders: [{ id: widgetId, order: parseInt(newOrder) }]
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Optional: show a brief success message
                            console.log('Order updated');
                        }
                    })
                    .catch(error => console.error('Error:', error));
                },

                editWidget(widgetId) {
                    // TODO: Open modal for editing widget
                    alert('Edit funktionalitet kommer snart. Widget ID: ' + widgetId);
                },

                deleteWidget(widgetId, widgetName) {
                    if (confirm(`Er du sikker på at du vil slette widget "${widgetName}"?\n\nDette vil også slette alle tilknyttede snapshots.`)) {
                        const form = document.getElementById('delete-form');
                        form.action = `/admin/widgets/${widgetId}`;
                        form.submit();
                    }
                }
            }
        }

        function updateUrlParam(key, value) {
            const url = new URL(window.location);
            if (value === 'all') {
                url.searchParams.delete(key);
            } else {
                url.searchParams.set(key, value);
            }
            return url.search;
        }
    </script>
</x-app-layout>
