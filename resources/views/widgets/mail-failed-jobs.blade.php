<div class="p-2">
    @if(isset($data['error']))
        <div class="text-center text-gray-500">
            <p class="text-xs">{{ $data['error'] }}</p>
        </div>
    @else
        <div class="mb-0.5">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-semibold">Failed Jobs</h4>
                <span class="px-2 py-1 text-xs rounded {{ $data['status'] === 'ok' ? 'bg-green-100 text-green-800' : ($data['status'] === 'critical' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                    {{ $data['total'] ?? 0 }} total
                </span>
            </div>
            <p class="text-xs text-gray-500 mt-1">{{ $data['recent_24h'] ?? 0 }} i siste 24t</p>
        </div>

        @if(isset($data['latest']) && count($data['latest']) > 0)
            <div class="space-y-0.5">
                @foreach(array_slice($data['latest'], 0, 3) as $job)
                    <div class="bg-red-50 border-l-4 border-red-500 p-2 rounded text-xs">
                        <p class="font-semibold text-red-900">{{ $job['queue'] }}</p>
                        <p class="text-red-700 truncate" title="{{ $job['error'] }}">{{ $job['error'] }}</p>
                        <p class="text-red-600 text-xs mt-1">{{ \Carbon\Carbon::parse($job['failed_at'])->diffForHumans() }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-1 text-green-600">
                <svg class="w-12 h-12 mx-auto mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-xs font-semibold">Ingen feil!</p>
            </div>
        @endif
    @endif
</div>
