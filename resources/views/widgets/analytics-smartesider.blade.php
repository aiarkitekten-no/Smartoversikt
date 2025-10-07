<div class="p-2">
    @if(isset($data['error']))
        <div class="text-center text-gray-500">
            <p class="text-xs">{{ $data['error'] }}</p>
        </div>
    @else
        <div class="mb-0.5">
            <h3 class="text-xs font-semibold text-gray-600 mb-0.5">Besøkende</h3>
            <div class="grid grid-cols-2 gap-1 text-xs">
                <div>
                    <p class="text-base font-bold text-blue-600">{{ number_format($data['visitors']['current'] ?? 0) }}</p>
                    <p class="text-xs text-gray-500">Nå</p>
                </div>
                <div>
                    <p class="text-base font-bold text-green-600">{{ number_format($data['visitors']['today'] ?? 0) }}</p>
                    <p class="text-xs text-gray-500">I dag</p>
                </div>
            </div>
        </div>

        <div class="space-y-0.5 text-xs">
            <div class="flex justify-between">
                <span class="text-gray-600">I går:</span>
                <span class="font-semibold">{{ number_format($data['visitors']['yesterday'] ?? 0) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Denne måned:</span>
                <span class="font-semibold">{{ number_format($data['visitors']['this_month'] ?? 0) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Sider/besøk:</span>
                <span class="font-semibold">{{ $data['pageviews']['per_visitor'] ?? 0 }}</span>
            </div>
        </div>

        @if(isset($data['top_pages']) && count($data['top_pages']) > 0)
            <div class="mt-0.5 pt-3 border-t">
                <p class="text-xs font-semibold text-gray-600 mb-1">Populære sider:</p>
                @foreach(array_slice($data['top_pages'], 0, 3) as $page)
                    <div class="flex justify-between text-xs py-1">
                        <span class="text-gray-600 truncate">{{ $page['url'] }}</span>
                        <span class="font-semibold ml-2">{{ number_format($page['views']) }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        @if(isset($data['note']))
            <p class="text-xs text-gray-400 mt-0.5 text-center">{{ $data['note'] }}</p>
        @endif
    @endif
</div>
