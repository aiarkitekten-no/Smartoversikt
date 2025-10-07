<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Innstillinger') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- IMAP Accounts -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-semibold">IMAP Kontoer</h3>
                            <p class="text-sm text-gray-600">Administrer e-postkontoer for IMAP-widgets</p>
                        </div>
                        <button onclick="document.getElementById('add-imap-form').classList.toggle('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                            + Legg til IMAP
                        </button>
                    </div>

                    <!-- Add IMAP Form (hidden by default) -->
                    <div id="add-imap-form" class="hidden mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <form method="POST" action="{{ route('settings.mail-accounts.store') }}">
                            @csrf
                            <input type="hidden" name="type" value="imap">

                            <h4 class="font-semibold mb-3">Ny IMAP-konto</h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Navn *</label>
                                    <input type="text" name="name" required placeholder="F.eks: Terje - Smartesider" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Brukernavn *</label>
                                    <input type="text" name="username" required placeholder="din@epost.no" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Server *</label>
                                    <input type="text" name="host" required placeholder="mail.smartesider.no" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Passord *</label>
                                    <input type="password" name="password" required 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Port *</label>
                                    <input type="number" name="port" required value="993" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Kryptering</label>
                                    <select name="encryption" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="ssl">SSL (port 993)</option>
                                        <option value="tls">TLS (port 143)</option>
                                        <option value="none">Ingen</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 mb-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="validate_cert" value="1" checked 
                                        class="rounded border-gray-300 text-blue-600">
                                    <span class="ml-2 text-sm">Valider SSL-sertifikat</span>
                                </label>

                                <label class="flex items-center">
                                    <input type="checkbox" name="is_active" value="1" checked 
                                        class="rounded border-gray-300 text-blue-600">
                                    <span class="ml-2 text-sm">Aktiv</span>
                                </label>
                            </div>

                            <div class="flex gap-3">
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    Legg til konto
                                </button>
                                <button type="button" onclick="document.getElementById('add-imap-form').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                                    Avbryt
                                </button>
                            </div>
                        </form>
                    </div>

                    @if($imapAccounts->isEmpty())
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-sm">Ingen IMAP-kontoer lagt til ennå</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($imapAccounts as $account)
                                <div class="border rounded-lg p-4 hover:bg-gray-50 transition">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <h4 class="font-semibold">{{ $account->name }}</h4>
                                                @if($account->is_active)
                                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded-full">Aktiv</span>
                                                @else
                                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-700 text-xs rounded-full">Inaktiv</span>
                                                @endif
                                            </div>
                                            <p class="text-sm text-gray-600">{{ $account->username }}</p>
                                            <p class="text-xs text-gray-500 mt-1">{{ $account->host }}:{{ $account->port }} ({{ strtoupper($account->encryption) }})</p>
                                        </div>
                                        <div class="flex gap-2">
                                            <button onclick="document.getElementById('edit-form-{{ $account->id }}').classList.toggle('hidden')" class="text-blue-600 hover:text-blue-700 text-sm">
                                                Rediger
                                            </button>
                                            <form method="POST" action="{{ route('settings.mail-accounts.destroy', $account) }}" class="inline" onsubmit="return confirm('Er du sikker?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-700 text-sm">
                                                    Slett
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Edit Form (hidden) -->
                                    <div id="edit-form-{{ $account->id }}" class="hidden mt-4 p-3 bg-gray-50 rounded border">
                                        <form method="POST" action="{{ route('settings.mail-accounts.update', $account) }}">
                                            @csrf
                                            @method('PATCH')

                                            <div class="grid grid-cols-2 gap-3 mb-3">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700">Navn</label>
                                                    <input type="text" name="name" value="{{ $account->name }}" required 
                                                        class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700">Brukernavn</label>
                                                    <input type="text" name="username" value="{{ $account->username }}" required 
                                                        class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700">Server</label>
                                                    <input type="text" name="host" value="{{ $account->host }}" required 
                                                        class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700">Passord (tomt = uendret)</label>
                                                    <input type="password" name="password" 
                                                        class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700">Port</label>
                                                    <input type="number" name="port" value="{{ $account->port }}" required 
                                                        class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700">Kryptering</label>
                                                    <select name="encryption" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                                                        <option value="ssl" {{ $account->encryption == 'ssl' ? 'selected' : '' }}>SSL</option>
                                                        <option value="tls" {{ $account->encryption == 'tls' ? 'selected' : '' }}>TLS</option>
                                                        <option value="none" {{ $account->encryption == 'none' ? 'selected' : '' }}>Ingen</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-4 mb-3">
                                                <label class="flex items-center">
                                                    <input type="checkbox" name="validate_cert" value="1" {{ $account->validate_cert ? 'checked' : '' }} 
                                                        class="rounded border-gray-300 text-blue-600">
                                                    <span class="ml-2 text-xs">Valider sertifikat</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox" name="is_active" value="1" {{ $account->is_active ? 'checked' : '' }} 
                                                        class="rounded border-gray-300 text-blue-600">
                                                    <span class="ml-2 text-xs">Aktiv</span>
                                                </label>
                                            </div>

                                            <div class="flex gap-2">
                                                <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                                                    Lagre
                                                </button>
                                                <button type="button" onclick="document.getElementById('edit-form-{{ $account->id }}').classList.add('hidden')" class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300">
                                                    Avbryt
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- RSS Feeds -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-semibold">RSS-Feeds</h3>
                            <p class="text-sm text-gray-600">Administrer RSS-kilder for nyhetswidgets</p>
                        </div>
                        <button onclick="document.getElementById('add-rss-form').classList.toggle('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                            + Legg til RSS-feed
                        </button>
                    </div>

                    <!-- Add RSS Form (hidden by default) -->
                    <div id="add-rss-form" class="hidden mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <form method="POST" action="{{ route('settings.rss-feeds.store') }}">
                            @csrf

                            <h4 class="font-semibold mb-3">Ny RSS-feed</h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Navn *</label>
                                    <input type="text" name="name" required placeholder="F.eks: NRK Nyheter" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Kategori</label>
                                    <input type="text" name="category" placeholder="F.eks: Nyheter, Sport, Teknologi" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">RSS URL *</label>
                                    <input type="url" name="url" required placeholder="https://www.example.com/feed/rss" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Oppdateringsintervall (sekunder)</label>
                                    <input type="number" name="refresh_interval" value="600" min="60" max="3600"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div class="flex items-center">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="is_active" value="1" checked 
                                            class="rounded border-gray-300 text-blue-600">
                                        <span class="ml-2 text-sm">Aktiv</span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    Legg til feed
                                </button>
                                <button type="button" onclick="document.getElementById('add-rss-form').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                                    Avbryt
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- RSS Feeds List -->
                    @if($rssFeeds->count() > 0)
                        <div class="space-y-3">
                            @foreach($rssFeeds as $feed)
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <h4 class="font-semibold text-gray-900">{{ $feed->name }}</h4>
                                                @if($feed->category)
                                                    <span class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded">{{ $feed->category }}</span>
                                                @endif
                                                @if(!$feed->is_active)
                                                    <span class="text-xs px-2 py-1 bg-gray-200 text-gray-600 rounded">Inaktiv</span>
                                                @endif
                                            </div>
                                            <p class="text-sm text-gray-600 break-all">{{ $feed->url }}</p>
                                            <p class="text-xs text-gray-500 mt-1">Oppdateres hvert {{ $feed->refresh_interval }}. sekund</p>
                                        </div>
                                        <div class="flex gap-2 ml-4">
                                            <button onclick="document.getElementById('edit-rss-{{ $feed->id }}').classList.toggle('hidden')" 
                                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                Rediger
                                            </button>
                                            <form method="POST" action="{{ route('settings.rss-feeds.destroy', $feed) }}" 
                                                onsubmit="return confirm('Er du sikker på at du vil slette denne RSS-feeden?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                    Slett
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Edit Form (hidden by default) -->
                                    <div id="edit-rss-{{ $feed->id }}" class="hidden mt-4 pt-4 border-t border-gray-200">
                                        <form method="POST" action="{{ route('settings.rss-feeds.update', $feed) }}">
                                            @csrf
                                            @method('PATCH')

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Navn *</label>
                                                    <input type="text" name="name" value="{{ $feed->name }}" required 
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Kategori</label>
                                                    <input type="text" name="category" value="{{ $feed->category }}" 
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                </div>

                                                <div class="md:col-span-2">
                                                    <label class="block text-sm font-medium text-gray-700">RSS URL *</label>
                                                    <input type="url" name="url" value="{{ $feed->url }}" required 
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Oppdateringsintervall (sekunder)</label>
                                                    <input type="number" name="refresh_interval" value="{{ $feed->refresh_interval }}" min="60" max="3600"
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                </div>

                                                <div class="flex items-center">
                                                    <label class="flex items-center">
                                                        <input type="checkbox" name="is_active" value="1" {{ $feed->is_active ? 'checked' : '' }}
                                                            class="rounded border-gray-300 text-blue-600">
                                                        <span class="ml-2 text-sm">Aktiv</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="flex gap-3">
                                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                                    Lagre endringer
                                                </button>
                                                <button type="button" onclick="document.getElementById('edit-rss-{{ $feed->id }}').classList.add('hidden')" 
                                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                                                    Avbryt
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 text-gray-500">
                            <p>Ingen RSS-feeds konfigurert</p>
                            <p class="text-sm mt-1">Klikk på "Legg til RSS-feed" for å komme i gang</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Weather Settings -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Vær Innstillinger</h3>
                    <p class="text-sm text-gray-600 mb-4">Standard lokasjon for værwidgets (Yr.no)</p>

                    <form method="POST" action="{{ route('settings.weather.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Breddegrad</label>
                                <input type="number" step="0.0001" name="weather_lat" value="{{ old('weather_lat', $weatherSettings['lat']) }}" required 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Lengdegrad</label>
                                <input type="number" step="0.0001" name="weather_lon" value="{{ old('weather_lon', $weatherSettings['lon']) }}" required 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Lokasjonsnavn</label>
                                <input type="text" name="weather_location" value="{{ old('weather_location', $weatherSettings['location']) }}" required 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <p class="text-xs text-gray-500 mb-4">
                            💡 Finn koordinater på <a href="https://www.yr.no" target="_blank" class="text-blue-600 hover:underline">yr.no</a> - URL-en inneholder lat/lon
                        </p>

                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                            Lagre værinnstillinger
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
