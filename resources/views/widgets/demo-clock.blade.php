@php
    $currentHour = (int) date('H');
    
    // Determine time of day
    if ($currentHour >= 5 && $currentHour < 9) {
        $timeOfDay = 'dawn'; // Morgengry 05:00-09:00
    } elseif ($currentHour >= 9 && $currentHour < 17) {
        $timeOfDay = 'day'; // Dag 09:00-17:00
    } elseif ($currentHour >= 17 && $currentHour < 20) {
        $timeOfDay = 'dusk'; // Kveldssol 17:00-20:00
    } else {
        $timeOfDay = 'night'; // Natt 20:00-05:00
    }
@endphp

<style>
    @keyframes dawn-gradient {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }
    
    @keyframes sun-rise {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-5px); }
    }
    
    @keyframes stars-twinkle {
        0%, 100% { opacity: 0.3; }
        50% { opacity: 1; }
    }
    
    @keyframes moon-glow {
        0%, 100% { opacity: 0.8; box-shadow: 0 0 20px rgba(255, 255, 255, 0.5); }
        50% { opacity: 1; box-shadow: 0 0 40px rgba(255, 255, 255, 0.8); }
    }
    
    @keyframes clouds-drift {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    
    .time-bg-dawn {
        background: linear-gradient(135deg, #FF6B6B 0%, #FFB88C 25%, #FFF3B0 50%, #87CEEB 100%);
        background-size: 200% 200%;
        animation: dawn-gradient 10s ease infinite;
    }
    
    .time-bg-day {
        background: linear-gradient(180deg, #87CEEB 0%, #B0E0E6 50%, #E0F6FF 100%);
    }
    
    .time-bg-dusk {
        background: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 25%, #FE9B72 50%, #4A5568 100%);
        background-size: 200% 200%;
        animation: dawn-gradient 12s ease infinite;
    }
    
    .time-bg-night {
        background: linear-gradient(180deg, #0F172A 0%, #1E293B 50%, #334155 100%);
    }
    
    .sun-icon {
        animation: sun-rise 4s ease-in-out infinite;
    }
    
    .star {
        position: absolute;
        background: white;
        border-radius: 50%;
        animation: stars-twinkle 3s ease-in-out infinite;
    }
    
    .moon {
        animation: moon-glow 6s ease-in-out infinite;
    }
    
    .cloud {
        position: absolute;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 50px;
        animation: clouds-drift 30s linear infinite;
    }
</style>

<div class="h-full flex flex-col rounded-lg shadow-sm p-6 relative overflow-hidden time-bg-{{ $timeOfDay }}" 
     x-data="widgetData('{{ $widget->key ?? 'demo.clock' }}')" 
     x-init="startRefresh(10)">
    
    <!-- Time of Day Decorations -->
    @if($timeOfDay === 'dawn')
        <!-- Rising Sun -->
        <div class="absolute top-4 right-4 w-12 h-12 bg-yellow-400 rounded-full sun-icon opacity-80"></div>
    @elseif($timeOfDay === 'day')
        <!-- Bright Sun -->
        <div class="absolute top-4 right-4 w-12 h-12 bg-yellow-300 rounded-full opacity-70"></div>
        <!-- Clouds -->
        <div class="cloud" style="top: 10px; left: -50%; width: 60px; height: 20px; animation-delay: 0s;"></div>
        <div class="cloud" style="top: 30px; left: -80%; width: 40px; height: 15px; animation-delay: 5s;"></div>
    @elseif($timeOfDay === 'dusk')
        <!-- Setting Sun -->
        <div class="absolute bottom-4 right-4 w-12 h-12 bg-orange-400 rounded-full sun-icon opacity-70"></div>
    @else
        <!-- Night: Moon and Stars -->
        <div class="absolute top-4 right-4 w-10 h-10 bg-gray-200 rounded-full moon"></div>
        @for($i = 0; $i < 15; $i++)
            @php
                $top = rand(10, 80);
                $left = rand(10, 90);
                $size = rand(2, 4);
                $delay = rand(0, 30) / 10;
            @endphp
            <div class="star" style="top: {{ $top }}%; left: {{ $left }}%; width: {{ $size }}px; height: {{ $size }}px; animation-delay: {{ $delay }}s;"></div>
        @endfor
    @endif
    
    <!-- Widget Header -->
    <div class="flex items-center justify-between mb-0.5 relative z-0">
        <div class="flex items-center space-x-1">
            <span class="text-lg">🕐</span>
            <h3 class="text-base font-semibold {{ $timeOfDay === 'night' ? 'text-white' : 'text-gray-800' }}">Live Klokke</h3>
        </div>
        <div class="flex items-center space-x-1">
            <span x-show="loading" class="text-xs {{ $timeOfDay === 'night' ? 'text-gray-300' : 'text-gray-500' }}">⟳</span>
            <span x-show="!loading && isFresh" class="h-2 w-2 bg-green-500 rounded-full" title="Live data"></span>
            <span x-show="!loading && !isFresh" class="h-2 w-2 bg-yellow-500 rounded-full" title="Utdatert"></span>
        </div>
    </div>

    <!-- Error State -->
    <div x-show="error" class="bg-red-50 border border-red-200 rounded p-3 mb-0.5 relative z-0">
        <p class="text-xs text-red-700" x-text="error"></p>
    </div>

    <!-- Widget Content -->
    <div x-show="!error && data" class="relative z-0">
        <!-- Time Display -->
        <div class="text-center mb-0.5">
            <div class="text-lg font-bold {{ $timeOfDay === 'night' ? 'text-yellow-300' : 'text-indigo-600' }}" x-text="data?.time || '--:--:--'"></div>
            <div class="text-xs {{ $timeOfDay === 'night' ? 'text-gray-300' : 'text-gray-600' }} mt-0.5" x-text="data?.date || 'Laster...'"></div>
        </div>

        <!-- Server Info -->
        <div class="grid grid-cols-2 gap-1 text-xs">
            <div class="bg-white bg-opacity-30 backdrop-blur-sm rounded p-3">
                <div class="text-xs {{ $timeOfDay === 'night' ? 'text-gray-300' : 'text-gray-500' }} mb-1">Server</div>
                <div class="font-medium {{ $timeOfDay === 'night' ? 'text-white' : 'text-gray-900' }}" x-text="data?.server?.hostname || 'N/A'"></div>
            </div>
            <div class="bg-white bg-opacity-30 backdrop-blur-sm rounded p-3">
                <div class="text-xs {{ $timeOfDay === 'night' ? 'text-gray-300' : 'text-gray-500' }} mb-1">PHP</div>
                <div class="font-medium {{ $timeOfDay === 'night' ? 'text-white' : 'text-gray-900' }}" x-text="data?.server?.php_version || 'N/A'"></div>
            </div>
            <div class="bg-white bg-opacity-30 backdrop-blur-sm rounded p-3">
                <div class="text-xs {{ $timeOfDay === 'night' ? 'text-gray-300' : 'text-gray-500' }} mb-1">Minne</div>
                <div class="font-medium {{ $timeOfDay === 'night' ? 'text-white' : 'text-gray-900' }}" x-text="data?.stats?.memory_usage || 'N/A'"></div>
            </div>
            <div class="bg-white bg-opacity-30 backdrop-blur-sm rounded p-3">
                <div class="text-xs {{ $timeOfDay === 'night' ? 'text-gray-300' : 'text-gray-500' }} mb-1">Peak</div>
                <div class="font-medium {{ $timeOfDay === 'night' ? 'text-white' : 'text-gray-900' }}" x-text="data?.stats?.memory_peak || 'N/A'"></div>
            </div>
        </div>

        <!-- Last Update -->
        <div class="mt-0.5 text-xs text-gray-500 text-center">
            Oppdatert: <span x-text="lastUpdate"></span>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading && !data" class="text-center py-1">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        <p class="text-xs text-gray-500 mt-0.5">Laster...</p>
    </div>
</div>
