<div class="p-2">
    @if(isset($data['error']))
        <div class="text-center text-gray-500">
            <p class="text-xs">{{ $data['error'] }}</p>
        </div>
    @else
        <div class="text-center mb-0.5">
            <p class="text-xs text-gray-500 mb-1">{{ $data['current_hour'] ?? '' }}</p>
            <p class="text-lg font-bold text-yellow-600">{{ $data['current_price'] ?? 0 }}</p>
            <p class="text-xs text-gray-600">{{ $data['unit'] ?? 'øre/kWh' }}</p>
        </div>

        <div class="mb-1">
            <p class="text-xs text-center px-2 py-1 bg-gray-100 rounded">
                {{ $data['recommendation'] ?? '' }}
            </p>
        </div>

        <div class="grid grid-cols-3 gap-1 text-xs text-center">
            <div class="bg-green-50 p-2 rounded">
                <p class="font-semibold text-green-700">{{ $data['min_price'] ?? 0 }}</p>
                <p class="text-gray-600">Min</p>
            </div>
            <div class="bg-blue-50 p-2 rounded">
                <p class="font-semibold text-blue-700">{{ $data['avg_price'] ?? 0 }}</p>
                <p class="text-gray-600">Snitt</p>
            </div>
            <div class="bg-red-50 p-2 rounded">
                <p class="font-semibold text-red-700">{{ $data['max_price'] ?? 0 }}</p>
                <p class="text-gray-600">Max</p>
            </div>
        </div>

        @if(isset($data['note']))
            <p class="text-xs text-gray-400 mt-0.5 text-center">{{ $data['note'] }}</p>
        @endif
    @endif
</div>
