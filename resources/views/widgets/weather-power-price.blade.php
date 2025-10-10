@php
    // Determine power price status
    $currentPrice = $data['current_price'] ?? 0;
    $avgPrice = $data['avg_price'] ?? 0;
    
    if ($currentPrice <= $avgPrice) {
        $priceStatus = 'cheap'; // Green - Below average!
    } else {
        $overPercent = (($currentPrice - $avgPrice) / $avgPrice) * 100;
        if ($overPercent <= 20) {
            $priceStatus = 'moderate'; // Yellow - Slightly expensive
        } else {
            $priceStatus = 'expensive'; // Red - Expensive!
        }
    }
@endphp

<style>
    .power-cheap {
        background: linear-gradient(135deg, #10B981 0%, #34D399 50%, #6EE7B7 100%);
    }
    
    .power-moderate {
        background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 50%, #FCD34D 100%);
    }
    
    .power-expensive {
        background: linear-gradient(135deg, #DC2626 0%, #EF4444 50%, #F87171 100%);
    }
</style>

<div class="p-2 rounded-xl border border-white/10 transition-all duration-300 power-{{ $priceStatus }}">
    @if(isset($data['error']))
        <div class="text-center text-white">
            <p class="text-xs">{{ $data['error'] }}</p>
        </div>
    @else
        <!-- Område info -->
        <div class="text-center mb-1">
            <p class="text-xs text-white/80">{{ $data['area_name'] ?? 'NO1' }} - {{ $data['current_hour'] ?? '' }}</p>
        </div>

        <!-- Nåværende pris -->
        <div class="text-center mb-2">
            <p class="text-2xl font-black text-white drop-shadow-lg">{{ number_format($data['current_price'] ?? 0, 2, ',', ' ') }}</p>
            <p class="text-xs text-white/70">{{ $data['unit'] ?? 'øre/kWh' }}</p>
            @if(isset($data['current_details']))
                <p class="text-xs text-white/60 mt-1">
                    ({{ number_format($data['current_details']['total_nok'], 4, ',', ' ') }} NOK/kWh)
                </p>
            @endif
        </div>

        <!-- Anbefaling -->
        <div class="mb-2">
            <p class="text-xs text-center px-2 py-1.5 bg-black/20 rounded font-medium text-white">
                {{ $data['recommendation'] ?? '' }}
            </p>
        </div>

        <!-- Min/Snitt/Max -->
        <div class="grid grid-cols-3 gap-1.5 text-xs text-center mb-2">
            <div class="bg-green-50 p-2 rounded">
                <p class="font-bold text-green-700">{{ number_format($data['min_price'] ?? 0, 2, ',', ' ') }}</p>
                <p class="text-gray-600 text-xs">Min</p>
                <p class="text-gray-500 text-xs">{{ $data['min_hour'] ?? '' }}</p>
            </div>
            <div class="bg-blue-50 p-2 rounded">
                <p class="font-bold text-blue-700">{{ number_format($data['avg_price'] ?? 0, 2, ',', ' ') }}</p>
                <p class="text-gray-600 text-xs">Snitt</p>
            </div>
            <div class="bg-red-50 p-2 rounded">
                <p class="font-bold text-red-700">{{ number_format($data['max_price'] ?? 0, 2, ',', ' ') }}</p>
                <p class="text-gray-600 text-xs">Max</p>
                <p class="text-gray-500 text-xs">{{ $data['max_hour'] ?? '' }}</p>
            </div>
        </div>

        <!-- Prisdetaljer -->
        @if(isset($data['current_details']))
            <div class="border-t pt-2 mt-2">
                <p class="text-xs text-gray-700 font-semibold mb-1">Prisberegning (nå):</p>
                <div class="text-xs space-y-0.5 text-white/90">
                    <div class="flex justify-between">
                        <span class="text-white/70">Grunnpris u/mva:</span>
                        <span class="font-mono">{{ number_format($data['current_details']['base_no_vat_nok'], 4, ',', ' ') }} kr</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-white/70">Med 25% mva:</span>
                        <span class="font-mono">{{ number_format($data['current_details']['with_vat_nok'], 4, ',', ' ') }} kr</span>
                    </div>
                    @if(isset($data['config']['markup_ore']) && $data['config']['markup_ore'] > 0)
                        <div class="flex justify-between">
                            <span class="text-white/70">Påslag:</span>
                            <span class="font-mono">{{ $data['config']['markup_ore'] }} øre</span>
                        </div>
                    @endif
                    <div class="flex justify-between font-semibold border-t border-white/20 pt-1">
                        <span class="text-white">Total pris:</span>
                        <span class="font-mono text-white drop-shadow">{{ number_format($data['current_details']['total_nok'], 4, ',', ' ') }} kr</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Time-for-time graf (footer) -->
        @if(isset($data['hourly']) && count($data['hourly']) > 0)
            <div class="border-t border-white/20 pt-2 mt-2">
                <p class="text-xs text-white font-semibold mb-1">Dagens priser:</p>
                <div class="flex items-end justify-between h-16 gap-px">
                    @php
                        $maxPriceForGraph = max(array_column($data['hourly'], 'ore_per_kwh'));
                        $minPriceForGraph = min(array_column($data['hourly'], 'ore_per_kwh'));
                        $range = $maxPriceForGraph - $minPriceForGraph;
                        $currentHourNum = isset($data['current_hour']) ? (int)substr($data['current_hour'], 0, 2) : -1;
                    @endphp
                    @foreach($data['hourly'] as $hour)
                        @php
                            $hourNum = (int)substr($hour['hour'], 0, 2);
                            $height = $range > 0 ? (($hour['ore_per_kwh'] - $minPriceForGraph) / $range * 100) : 50;
                            $isCurrent = $hourNum === $currentHourNum;
                            
                            // Fargekoding basert på pris
                            if ($hour['ore_per_kwh'] <= $data['min_price'] * 1.1) {
                                $color = 'bg-green-500';
                            } elseif ($hour['ore_per_kwh'] >= $data['max_price'] * 0.9) {
                                $color = 'bg-red-500';
                            } else {
                                $color = 'bg-yellow-500';
                            }
                        @endphp
                        <div 
                            class="flex-1 {{ $color }} {{ $isCurrent ? 'ring-2 ring-blue-600' : '' }} rounded-t"
                            style="height: {{ max($height, 10) }}%"
                            title="{{ $hour['hour'] }}: {{ number_format($hour['ore_per_kwh'], 2, ',', ' ') }} øre/kWh"
                        ></div>
                    @endforeach
                </div>
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                    <span>00</span>
                    <span>06</span>
                    <span>12</span>
                    <span>18</span>
                    <span>23</span>
                </div>
            </div>
        @endif
    @endif
</div>
