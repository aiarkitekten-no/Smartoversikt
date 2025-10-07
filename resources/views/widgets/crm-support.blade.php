<div class="p-2">
    @if(isset($data['error']))
        <div class="text-center text-gray-500">
            <p class="text-xs">{{ $data['error'] }}</p>
        </div>
    @else
        <div class="grid grid-cols-2 gap-1 mb-0.5 text-center text-xs">
            <div class="bg-red-50 p-2 rounded">
                <p class="text-base font-bold text-red-700">{{ $data['tickets']['open'] ?? 0 }}</p>
                <p class="text-gray-600">Åpne</p>
            </div>
            <div class="bg-yellow-50 p-2 rounded">
                <p class="text-base font-bold text-yellow-700">{{ $data['tickets']['pending'] ?? 0 }}</p>
                <p class="text-gray-600">Venter</p>
            </div>
            <div class="bg-green-50 p-2 rounded">
                <p class="text-base font-bold text-green-700">{{ $data['tickets']['solved_today'] ?? 0 }}</p>
                <p class="text-gray-600">Løst i dag</p>
            </div>
            <div class="bg-blue-50 p-2 rounded">
                <p class="text-base font-bold text-blue-700">{{ $data['tickets']['new_today'] ?? 0 }}</p>
                <p class="text-gray-600">Nye i dag</p>
            </div>
        </div>

        <div class="space-y-1 text-xs mb-1">
            <div class="flex justify-between">
                <span class="text-gray-600">Avg responstid:</span>
                <span class="font-semibold">{{ $data['response_time']['avg_first_response_hours'] ?? 0 }}t</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Kundetilfredshet:</span>
                <span class="font-semibold text-green-600">{{ $data['satisfaction']['score'] ?? 0 }}%</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Agenter online:</span>
                <span class="font-semibold">{{ $data['agents']['online'] ?? 0 }}/{{ $data['agents']['total'] ?? 0 }}</span>
            </div>
        </div>

        @if(isset($data['priority']))
            <div class="pt-2 border-t">
                <p class="text-xs font-semibold text-gray-600 mb-1">Prioritet:</p>
                <div class="flex gap-1 text-xs">
                    @if(($data['priority']['urgent'] ?? 0) > 0)
                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded">🔴 {{ $data['priority']['urgent'] }}</span>
                    @endif
                    @if(($data['priority']['high'] ?? 0) > 0)
                        <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded">🟠 {{ $data['priority']['high'] }}</span>
                    @endif
                    <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded">⚪ {{ $data['priority']['normal'] ?? 0 }}</span>
                </div>
            </div>
        @endif

        @if(isset($data['note']))
            <p class="text-xs text-gray-400 mt-0.5 text-center">{{ $data['note'] }}</p>
        @endif
    @endif
</div>
