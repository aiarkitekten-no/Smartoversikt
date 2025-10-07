<div class="p-2">
    @if(isset($data['error']))
        <div class="text-center text-red-600">
            <p class="font-semibold">{{ $data['error'] }}</p>
        </div>
    @else
        <div class="grid grid-cols-2 gap-1">
            <!-- Laravel Queue -->
            <div class="bg-blue-50 p-3 rounded">
                <h4 class="text-xs font-semibold text-blue-900 mb-0.5">Laravel Queue</h4>
                <div class="space-y-1">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-600">Pending:</span>
                        <span class="font-semibold">{{ $data['laravel']['pending'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-600">Failed:</span>
                        <span class="font-semibold text-red-600">{{ $data['laravel']['failed'] ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <!-- System Queue -->
            <div class="bg-green-50 p-3 rounded">
                <h4 class="text-xs font-semibold text-green-900 mb-0.5">System Mail</h4>
                <div class="space-y-1">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-600">Deferred:</span>
                        <span class="font-semibold">{{ $data['system']['deferred'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-600">Active:</span>
                        <span class="font-semibold text-green-600">{{ $data['system']['active'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-0.5 pt-4 border-t border-gray-200">
            <div class="text-center">
                <p class="text-base font-bold text-gray-800">{{ $data['total_pending'] ?? 0 }}</p>
                <p class="text-xs text-gray-500">Total pending</p>
            </div>
        </div>

        @if(isset($data['system']['note']))
            <div class="mt-0.5 pt-3 border-t border-gray-200">
                <p class="text-xs text-gray-500 italic text-center">{{ $data['system']['note'] }}</p>
            </div>
        @endif
    @endif
</div>
