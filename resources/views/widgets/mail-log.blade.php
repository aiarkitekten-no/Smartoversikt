<div class="p-2">
    @if(isset($data['error']))
        <div class="text-center text-gray-500">
            <p class="text-xs">{{ $data['error'] }}</p>
        </div>
    @else
        <div class="grid grid-cols-3 gap-1 mb-0.5">
            <div class="text-center p-2 bg-green-50 rounded">
                <p class="text-base font-bold text-green-700">{{ $data['sent'] ?? 0 }}</p>
                <p class="text-xs text-gray-600">Sendt</p>
            </div>
            <div class="text-center p-2 bg-blue-50 rounded">
                <p class="text-base font-bold text-blue-700">{{ $data['received'] ?? 0 }}</p>
                <p class="text-xs text-gray-600">Mottatt</p>
            </div>
            <div class="text-center p-2 bg-red-50 rounded">
                <p class="text-base font-bold text-red-700">{{ $data['bounced'] ?? 0 }}</p>
                <p class="text-xs text-gray-600">Bounced</p>
            </div>
        </div>

        <div class="space-y-0.5 text-xs">
            <div class="flex justify-between">
                <span class="text-gray-600">Rejected:</span>
                <span class="font-semibold">{{ $data['rejected'] ?? 0 }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Deferred:</span>
                <span class="font-semibold">{{ $data['deferred'] ?? 0 }}</span>
            </div>
            <div class="flex justify-between pt-2 border-t">
                <span class="text-gray-600">Total:</span>
                <span class="font-bold">{{ $data['total'] ?? 0 }}</span>
            </div>
        </div>

        @if(isset($data['log_file']))
            <p class="text-xs text-gray-400 mt-0.5">Fra: {{ $data['log_file'] }}</p>
        @endif
    @endif
</div>
