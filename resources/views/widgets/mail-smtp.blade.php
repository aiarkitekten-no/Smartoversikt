<div class="p-2">
    @if(isset($data['error']) || !isset($data['running']))
        <div class="text-center text-gray-500">
            <p class="text-xs">{{ $data['error'] ?? 'Widget ikke lastet ennå' }}</p>
        </div>
    @else
        <div class="text-center mb-0.5">
            <div class="inline-block p-3 rounded-full {{ $data['running'] ? 'bg-green-100' : 'bg-red-100' }}">
                <svg class="w-8 h-8 {{ $data['running'] ? 'text-green-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if($data['running'])
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    @endif
                </svg>
            </div>
            <h3 class="text-base font-semibold mt-0.5">{{ $data['service'] ?? 'SMTP' }}</h3>
            <p class="text-xs {{ $data['running'] ? 'text-green-600' : 'text-red-600' }}">
                {{ $data['running'] ? 'Running' : 'Stopped' }}
            </p>
        </div>

        <div class="space-y-0.5 text-xs">
            <div class="flex justify-between">
                <span class="text-gray-600">Active connections:</span>
                <span class="font-semibold">{{ $data['active_connections'] ?? 0 }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Queue size:</span>
                <span class="font-semibold {{ ($data['queue_size'] ?? 0) > 100 ? 'text-yellow-600' : '' }}">
                    {{ $data['queue_size'] ?? 0 }}
                </span>
            </div>
        </div>

        @if(isset($data['note']))
            <div class="mt-0.5 pt-3 border-t border-gray-200">
                <p class="text-xs text-gray-500 italic">{{ $data['note'] }}</p>
            </div>
        @endif
    @endif
</div>
