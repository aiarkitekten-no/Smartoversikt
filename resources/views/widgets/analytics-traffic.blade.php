<div class="p-2">
    @if(isset($data['error']))
        <div class="text-center text-gray-500">
            <p class="text-xs">{{ $data['error'] }}</p>
        </div>
    @else
        <div class="grid grid-cols-2 gap-1 mb-0.5">
            <div class="text-center p-2 bg-blue-50 rounded">
                <p class="text-base font-bold text-blue-700">{{ $data['current_users'] ?? 0 }}</p>
                <p class="text-xs text-gray-600">Aktive nå</p>
            </div>
            <div class="text-center p-2 bg-green-50 rounded">
                <p class="text-base font-bold text-green-700">{{ $data['requests_per_minute'] ?? 0 }}</p>
                <p class="text-xs text-gray-600">Req/min</p>
            </div>
        </div>

        <div class="space-y-0.5 text-xs mb-1">
            <div class="flex justify-between">
                <span class="text-gray-600">Bandwidth:</span>
                <span class="font-semibold">{{ $data['bandwidth']['current_mbps'] ?? 0 }} Mbps</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Total i dag:</span>
                <span class="font-semibold">{{ $data['bandwidth']['total_today_gb'] ?? 0 }} GB</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Avg responstid:</span>
                <span class="font-semibold">{{ $data['response_time']['avg_ms'] ?? 0 }} ms</span>
            </div>
        </div>

        @if(isset($data['http_status']))
            <div class="pt-2 border-t">
                <p class="text-xs font-semibold text-gray-600 mb-1">HTTP Status:</p>
                <div class="flex gap-1 text-xs">
                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded">2xx: {{ $data['http_status']['2xx'] }}%</span>
                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded">4xx: {{ $data['http_status']['4xx'] }}%</span>
                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded">5xx: {{ $data['http_status']['5xx'] }}%</span>
                </div>
            </div>
        @endif

        @if(isset($data['note']))
            <p class="text-xs text-gray-400 mt-0.5 text-center">{{ $data['note'] }}</p>
        @endif
    @endif
</div>
