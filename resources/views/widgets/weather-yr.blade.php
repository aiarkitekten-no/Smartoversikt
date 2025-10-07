<div class="h-full flex flex-col p-2 relative overflow-hidden weather-widget" data-condition="{{ $data['condition'] ?? 'unknown' }}">
    
    <!-- Animated Background Effects -->
    <div class="weather-background absolute inset-0 pointer-events-none">
        <!-- Sun rays (for clear weather) -->
        <div class="sun-rays" data-show="{{ str_contains($data['condition'] ?? '', 'clearsky') || str_contains($data['condition'] ?? '', 'fair') ? 'true' : 'false' }}">
            @for($i = 0; $i < 8; $i++)
                <div class="sun-ray" style="--ray-index: {{ $i }};"></div>
            @endfor
        </div>
        
        <!-- Clouds (for cloudy weather) -->
        <div class="clouds" data-show="{{ str_contains($data['condition'] ?? '', 'cloud') || str_contains($data['condition'] ?? '', 'partly') ? 'true' : 'false' }}">
            <div class="cloud cloud-1"></div>
            <div class="cloud cloud-2"></div>
            <div class="cloud cloud-3"></div>
        </div>
        
        <!-- Rain (for rainy weather) -->
        <div class="rain" data-show="{{ str_contains($data['condition'] ?? '', 'rain') || str_contains($data['condition'] ?? '', 'drizzle') ? 'true' : 'false' }}">
            @for($i = 0; $i < 20; $i++)
                <div class="raindrop" style="left: {{ $i * 5 }}%; animation-delay: {{ $i * 0.1 }}s;"></div>
            @endfor
        </div>
        
        <!-- Snow (for snowy weather) -->
        <div class="snow" data-show="{{ str_contains($data['condition'] ?? '', 'snow') || str_contains($data['condition'] ?? '', 'sleet') ? 'true' : 'false' }}">
            @for($i = 0; $i < 15; $i++)
                <div class="snowflake" style="left: {{ $i * 7 }}%; animation-delay: {{ $i * 0.2 }}s;">❄</div>
            @endfor
        </div>
        
        <!-- Thunder/Lightning (for thunderstorms) -->
        <div class="lightning" data-show="{{ str_contains($data['condition'] ?? '', 'thunder') ? 'true' : 'false' }}">
            <div class="lightning-bolt"></div>
        </div>
        
        <!-- Fog (for fog/mist) -->
        <div class="fog" data-show="{{ str_contains($data['condition'] ?? '', 'fog') || str_contains($data['condition'] ?? '', 'mist') ? 'true' : 'false' }}">
            <div class="fog-layer fog-layer-1"></div>
            <div class="fog-layer fog-layer-2"></div>
            <div class="fog-layer fog-layer-3"></div>
        </div>
    </div>
    
    <!-- Widget Content (on top of background) -->
    <div class="relative z-0">
        @if(isset($data['error']))
            <div class="text-center text-gray-500">
                <p class="text-xs">{{ $data['error'] }}</p>
            </div>
        @else
            <div class="text-center mb-0.5">
                <h3 class="text-base font-semibold text-white drop-shadow-lg">{{ $data['location'] ?? 'Oslo' }}</h3>
                <p class="font-bold text-white my-2 drop-shadow-lg" style="font-size: 2.5rem; line-height: 1;">{{ $data['temperature'] ?? 0 }}°</p>
                <p class="text-sm text-white/90 drop-shadow">{{ $data['condition_text'] ?? 'Ukjent' }}</p>
                <p class="text-xs text-white/80 drop-shadow">Føles som {{ $data['feels_like'] ?? 0 }}°</p>
            </div>

            <div class="grid grid-cols-2 gap-1 text-xs backdrop-blur-sm bg-white/20 rounded p-1.5 mt-1">
                <div class="flex items-center text-white">
                    <span class="mr-2">💧</span>
                    <span class="drop-shadow">{{ $data['humidity'] ?? 0 }}%</span>
                </div>
                <div class="flex items-center text-white">
                    <span class="mr-2">��</span>
                    <span class="drop-shadow">{{ $data['wind_speed'] ?? 0 }} m/s</span>
                </div>
                @if(($data['precipitation'] ?? 0) > 0)
                    <div class="flex items-center col-span-2 text-white">
                        <span class="mr-2">🌧️</span>
                        <span class="drop-shadow">{{ $data['precipitation'] }} mm neste time</span>
                    </div>
                @endif
            </div>

            <p class="text-xs text-white/60 mt-0.5 text-center drop-shadow">Yr.no / MET</p>
        @endif
    </div>
</div>

<style>
.weather-widget {
    min-height: 180px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 8px;
}

.weather-widget[data-condition*="clearsky"],
.weather-widget[data-condition*="fair"] {
    background: linear-gradient(135deg, #FDB813 0%, #F59E0B 50%, #F97316 100%);
}

.weather-widget[data-condition*="cloud"],
.weather-widget[data-condition*="partly"] {
    background: linear-gradient(135deg, #64748b 0%, #94a3b8 100%);
}

.weather-widget[data-condition*="rain"],
.weather-widget[data-condition*="drizzle"] {
    background: linear-gradient(135deg, #0EA5E9 0%, #0284c7 100%);
}

.weather-widget[data-condition*="snow"],
.weather-widget[data-condition*="sleet"] {
    background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
}

.weather-widget[data-condition*="thunder"] {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
}

.weather-widget[data-condition*="fog"],
.weather-widget[data-condition*="mist"] {
    background: linear-gradient(135deg, #9ca3af 0%, #d1d5db 100%);
}

.sun-rays {
    display: none;
    position: absolute;
    top: 10%;
    right: 10%;
    width: 60px;
    height: 60px;
}

.sun-rays[data-show="true"] {
    display: block;
}

.sun-ray {
    position: absolute;
    width: 4px;
    height: 20px;
    background: rgba(255, 255, 255, 0.6);
    top: 50%;
    left: 50%;
    transform-origin: center -10px;
    transform: rotate(calc(var(--ray-index) * 45deg));
    animation: sun-pulse 2s ease-in-out infinite;
    border-radius: 2px;
}

@keyframes sun-pulse {
    0%, 100% { opacity: 0.6; transform: rotate(calc(var(--ray-index) * 45deg)) scale(1); }
    50% { opacity: 1; transform: rotate(calc(var(--ray-index) * 45deg)) scale(1.2); }
}

.clouds {
    display: none;
}

.clouds[data-show="true"] {
    display: block;
}

.cloud {
    position: absolute;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 50px;
    opacity: 0.7;
}

.cloud::before,
.cloud::after {
    content: '';
    position: absolute;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
}

.cloud-1 {
    width: 50px;
    height: 15px;
    top: 20%;
    animation: cloud-drift-1 15s linear infinite;
}

.cloud-1::before {
    width: 25px;
    height: 25px;
    top: -10px;
    left: 10px;
}

.cloud-1::after {
    width: 20px;
    height: 20px;
    top: -8px;
    right: 10px;
}

.cloud-2 {
    width: 40px;
    height: 12px;
    top: 50%;
    animation: cloud-drift-2 20s linear infinite;
}

.cloud-2::before {
    width: 20px;
    height: 20px;
    top: -8px;
    left: 8px;
}

.cloud-3 {
    width: 35px;
    height: 10px;
    top: 70%;
    animation: cloud-drift-3 18s linear infinite;
}

.cloud-3::before {
    width: 18px;
    height: 18px;
    top: -7px;
    left: 7px;
}

@keyframes cloud-drift-1 {
    0% { left: -60px; }
    100% { left: 110%; }
}

@keyframes cloud-drift-2 {
    0% { left: -50px; }
    100% { left: 110%; }
}

@keyframes cloud-drift-3 {
    0% { left: -40px; }
    100% { left: 110%; }
}

.rain {
    display: none;
}

.rain[data-show="true"] {
    display: block;
}

.raindrop {
    position: absolute;
    width: 2px;
    height: 15px;
    background: linear-gradient(to bottom, transparent, rgba(255, 255, 255, 0.6));
    animation: rain-fall 1s linear infinite;
    top: -20px;
}

@keyframes rain-fall {
    0% { top: -20px; opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { top: 100%; opacity: 0; }
}

.snow {
    display: none;
}

.snow[data-show="true"] {
    display: block;
}

.snowflake {
    position: absolute;
    color: rgba(255, 255, 255, 0.8);
    font-size: 14px;
    animation: snow-fall 3s linear infinite;
    top: -20px;
}

@keyframes snow-fall {
    0% { 
        top: -20px; 
        transform: translateX(0) rotate(0deg);
    }
    100% { 
        top: 100%; 
        transform: translateX(20px) rotate(360deg);
    }
}

.lightning {
    display: none;
}

.lightning[data-show="true"] {
    display: block;
}

.lightning-bolt {
    position: absolute;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0);
    animation: lightning-flash 4s ease-in-out infinite;
}

@keyframes lightning-flash {
    0%, 10%, 20%, 100% { background: rgba(255, 255, 255, 0); }
    5%, 15% { background: rgba(255, 255, 255, 0.8); }
}

.fog {
    display: none;
}

.fog[data-show="true"] {
    display: block;
}

.fog-layer {
    position: absolute;
    width: 200%;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    filter: blur(8px);
}

.fog-layer-1 {
    top: 20%;
    animation: fog-drift-1 20s linear infinite;
}

.fog-layer-2 {
    top: 50%;
    animation: fog-drift-2 25s linear infinite;
}

.fog-layer-3 {
    top: 70%;
    animation: fog-drift-3 30s linear infinite;
}

@keyframes fog-drift-1 {
    0% { left: -100%; }
    100% { left: 0%; }
}

@keyframes fog-drift-2 {
    0% { left: 0%; }
    100% { left: 100%; }
}

@keyframes fog-drift-3 {
    0% { left: -50%; }
    100% { left: 50%; }
}

.drop-shadow {
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.drop-shadow-lg {
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
}
</style>
