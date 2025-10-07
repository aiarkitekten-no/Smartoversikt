<div class="p-2">
    @if(isset($data['error']))
        <div class="text-center text-gray-500">
            <p class="text-xs">{{ $data['error'] }}</p>
        </div>
    @else
        <div class="mb-0.5">
            <div class="grid grid-cols-3 gap-1 text-center text-xs mb-1">
                <div class="bg-blue-50 p-2 rounded">
                    <p class="text-base font-bold text-blue-700">{{ $data['deals']['open'] ?? 0 }}</p>
                    <p class="text-gray-600">Åpne deals</p>
                </div>
                <div class="bg-green-50 p-2 rounded">
                    <p class="text-base font-bold text-green-700">{{ $data['deals']['won_this_month'] ?? 0 }}</p>
                    <p class="text-gray-600">Vunnet</p>
                </div>
                <div class="bg-red-50 p-2 rounded">
                    <p class="text-base font-bold text-red-700">{{ $data['deals']['lost_this_month'] ?? 0 }}</p>
                    <p class="text-gray-600">Tapt</p>
                </div>
            </div>

            <div class="space-y-1 text-xs">
                <div class="flex justify-between">
                    <span class="text-gray-600">Verdi åpne:</span>
                    <span class="font-semibold">{{ number_format($data['deals']['value_open'] ?? 0, 0, ',', ' ') }} kr</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Vunnet denne mnd:</span>
                    <span class="font-semibold text-green-600">{{ number_format($data['deals']['value_won_this_month'] ?? 0, 0, ',', ' ') }} kr</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Konvertering:</span>
                    <span class="font-semibold">{{ $data['conversion_rate'] ?? 0 }}%</span>
                </div>
            </div>
        </div>

        @if(isset($data['recent_wins']) && count($data['recent_wins']) > 0)
            <div class="pt-3 border-t">
                <p class="text-xs font-semibold text-gray-600 mb-0.5">Siste vinn:</p>
                @foreach(array_slice($data['recent_wins'], 0, 2) as $win)
                    <div class="mb-0.5 pb-2 border-b border-gray-100 last:border-0">
                        <p class="text-xs font-semibold text-gray-800 truncate">{{ $win['name'] }}</p>
                        <div class="flex justify-between text-xs text-gray-600">
                            <span>{{ number_format($win['value'], 0, ',', ' ') }} kr</span>
                            <span>{{ \Carbon\Carbon::parse($win['date'])->diffForHumans() }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if(isset($data['note']))
            <p class="text-xs text-gray-400 mt-0.5 text-center">{{ $data['note'] }}</p>
        @endif
    @endif
</div>
