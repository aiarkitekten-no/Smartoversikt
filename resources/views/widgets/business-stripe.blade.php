@php
    $performanceStatus = $data['performance_status'] ?? 'on_track';
    $monthlyProgress = $data['this_month']['total'] ?? 0;
    $monthlyGoal = $data['monthly_goal'] ?? 150000;
    $progressPercentage = $monthlyGoal > 0 ? min(100, ($monthlyProgress / $monthlyGoal) * 100) : 0;
    
    // Determine status styling
    if ($performanceStatus === 'exceeded') {
        $statusClass = 'status-exceeded';
        $statusIcon = '🎉';
        $statusText = 'Goal Exceeded!';
    } elseif ($performanceStatus === 'on_track') {
        $statusClass = 'status-on-track';
        $statusIcon = '📈';
        $statusText = 'On Track';
    } elseif ($performanceStatus === 'behind') {
        $statusClass = 'status-behind';
        $statusIcon = '⚠️';
        $statusText = 'Behind Schedule';
    } else {
        $statusClass = 'status-critical';
        $statusIcon = '🚨';
        $statusText = 'Critical - Push Harder!';
    }
@endphp

<style>
    @keyframes coin-rain {
        0% { transform: translateY(-100%) rotate(0deg); opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 1; }
        100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
    }
    
    @keyframes cash-register {
        0%, 100% { transform: scale(1); }
        10% { transform: scale(1.2) rotate(-5deg); }
        20% { transform: scale(0.9) rotate(5deg); }
        30% { transform: scale(1.1) rotate(-3deg); }
        40% { transform: scale(1); }
    }
    
    @keyframes money-pulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.05); opacity: 0.8; }
    }
    
    @keyframes progress-fill {
        from { width: 0%; }
        to { width: var(--progress); }
    }
    
    @keyframes sparkle-pop {
        0% { transform: scale(0) rotate(0deg); opacity: 0; }
        50% { transform: scale(1.2) rotate(180deg); opacity: 1; }
        100% { transform: scale(0) rotate(360deg); opacity: 0; }
    }
    
    @keyframes transaction-slide {
        from { transform: translateX(-100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes number-count-up {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    @keyframes celebration-burst {
        0% { transform: scale(0); opacity: 1; }
        50% { transform: scale(2); opacity: 0.7; }
        100% { transform: scale(3); opacity: 0; }
    }
    
    .status-exceeded {
        background: linear-gradient(135deg, #059669 0%, #10B981 50%, #34D399 100%);
    }
    
    .status-on-track {
        background: linear-gradient(135deg, #0EA5E9 0%, #38BDF8 50%, #7DD3FC 100%);
    }
    
    .status-behind {
        background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 50%, #FCD34D 100%);
    }
    
    .status-critical {
        background: linear-gradient(135deg, #DC2626 0%, #EF4444 50%, #F87171 100%);
    }
    
    .coin {
        position: absolute;
        font-size: 24px;
        animation: coin-rain 3s linear infinite;
        pointer-events: none;
    }
    
    .cash-register-icon {
        animation: cash-register 2s ease-in-out infinite;
    }
    
    .money-amount {
        animation: number-count-up 0.8s ease-out forwards;
    }
    
    .progress-bar-fill {
        animation: progress-fill 2s ease-out forwards;
    }
    
    .sparkle {
        position: absolute;
        animation: sparkle-pop 1.5s ease-out infinite;
    }
    
    .transaction-item {
        animation: transaction-slide 0.5s ease-out forwards;
    }
    
    .celebration-ring {
        position: absolute;
        border: 3px solid rgba(255, 255, 255, 0.5);
        border-radius: 50%;
        animation: celebration-burst 2s ease-out infinite;
    }
</style>

<div class="overflow-hidden shadow-sm sm:rounded-lg relative {{ $statusClass }}">
    <!-- Coin Rain Effect (only when exceeded) -->
    @if($performanceStatus === 'exceeded')
        @for($i = 0; $i < 15; $i++)
            @php
                $left = rand(0, 100);
                $delay = rand(0, 30) / 10;
                $duration = rand(25, 40) / 10;
            @endphp
            <div class="coin" style="left: {{ $left }}%; animation-delay: {{ $delay }}s; animation-duration: {{ $duration }}s;">
                {{ ['💰', '💵', '💴', '💶', '💷', '🪙'][array_rand(['💰', '💵', '💴', '💶', '💷', '🪙'])] }}
            </div>
        @endfor
        
        <!-- Celebration Rings -->
        @for($i = 0; $i < 3; $i++)
            <div class="celebration-ring" style="top: 20%; left: 20%; width: 100px; height: 100px; animation-delay: {{ $i * 0.5 }}s;"></div>
        @endfor
    @endif
    
    <!-- Sparkles for good performance -->
    @if($performanceStatus === 'exceeded' || $performanceStatus === 'on_track')
        @for($i = 0; $i < 8; $i++)
            @php
                $top = rand(10, 90);
                $left = rand(10, 90);
                $delay = rand(0, 15) / 10;
            @endphp
            <div class="sparkle" style="top: {{ $top }}%; left: {{ $left }}%; animation-delay: {{ $delay }}s;">
                ✨
            </div>
        @endfor
    @endif
    
    <div class="p-2 relative z-0">
        <!-- Header -->
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                <span class="text-2xl cash-register-icon">{{ $statusIcon }}</span>
                <div>
                    <h3 class="text-sm font-bold text-white drop-shadow-lg">Stripe Dashboard</h3>
                    <p class="text-xs text-white text-opacity-90 drop-shadow">{{ $statusText }}</p>
                </div>
            </div>
        </div>
        
        <!-- Today's Sales - Big Number -->
        <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-3 mb-2">
            <div class="text-center">
                <div class="text-xs text-white text-opacity-80 mb-1">Dagens salg</div>
                <div class="text-3xl font-bold text-white drop-shadow-lg money-amount">
                    {{ number_format($data['today']['total'] ?? 0, 0, ',', ' ') }} kr
                </div>
                <div class="flex items-center justify-center gap-3 mt-2 text-xs text-white text-opacity-90">
                    <span>{{ $data['today']['count'] ?? 0 }} transaksjoner</span>
                    <span>•</span>
                    <span>Ø {{ number_format($data['today']['average_order'] ?? 0, 0) }} kr</span>
                </div>
            </div>
        </div>
        
        <!-- Monthly Progress Bar -->
        <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-3 mb-2">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs text-white text-opacity-80">Månedlig mål</span>
                <span class="text-xs font-bold text-white drop-shadow">
                    {{ number_format($progressPercentage, 1) }}%
                </span>
            </div>
            
            <div class="w-full bg-white bg-opacity-30 rounded-full h-3 mb-2">
                <div class="progress-bar-fill h-3 rounded-full bg-white shadow-lg" 
                     style="--progress: {{ $progressPercentage }}%;"></div>
            </div>
            
            <div class="flex items-center justify-between text-xs text-white text-opacity-90">
                <span>{{ number_format($monthlyProgress, 0, ',', ' ') }} kr</span>
                <span>{{ number_format($monthlyGoal, 0, ',', ' ') }} kr</span>
            </div>
        </div>
        
        <!-- Stats Grid -->
        <div class="grid grid-cols-2 gap-1 mb-2">
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-2">
                <div class="text-xs text-white text-opacity-80">I går</div>
                <div class="text-lg font-bold text-white drop-shadow">
                    {{ number_format($data['yesterday']['total'] ?? 0, 0, ',', ' ') }}
                </div>
                <div class="text-xs text-white text-opacity-70">
                    {{ $data['yesterday']['count'] ?? 0 }} transaksjoner
                </div>
            </div>
            
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-2">
                <div class="text-xs text-white text-opacity-80">Forrige måned</div>
                <div class="text-lg font-bold text-white drop-shadow">
                    {{ number_format($data['last_month']['total'] ?? 0, 0, ',', ' ') }}
                </div>
                <div class="text-xs text-white text-opacity-70">
                    {{ $data['last_month']['count'] ?? 0 }} transaksjoner
                </div>
            </div>
        </div>
        
        <!-- Top Products -->
        @if(isset($data['top_products']) && count($data['top_products']) > 0)
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-2 mb-2">
                <div class="text-xs text-white text-opacity-80 font-semibold mb-1">🏆 Toppselgere</div>
                <div class="space-y-1">
                    @foreach($data['top_products'] as $index => $product)
                        <div class="flex items-center justify-between text-xs transaction-item"
                             style="animation-delay: {{ $index * 0.1 }}s;">
                            <span class="text-white drop-shadow truncate flex-1">
                                {{ $index + 1 }}. {{ $product['name'] }}
                            </span>
                            <span class="text-white font-semibold drop-shadow ml-2">
                                {{ number_format($product['revenue'], 0, ',', ' ') }} kr
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        
        <!-- Recent Transactions -->
        @if(isset($data['recent_transactions']) && count($data['recent_transactions']) > 0)
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-2">
                <div class="text-xs text-white text-opacity-80 font-semibold mb-1">💳 Siste transaksjoner</div>
                <div class="space-y-1 max-h-32 overflow-y-auto">
                    @foreach($data['recent_transactions'] as $index => $transaction)
                        <div class="flex items-center justify-between text-xs transaction-item bg-white bg-opacity-10 rounded p-1"
                             style="animation-delay: {{ $index * 0.15 }}s;">
                            <div class="flex-1 min-w-0">
                                <div class="text-white drop-shadow truncate">{{ $transaction['product'] }}</div>
                                <div class="text-white text-opacity-70 text-xs">
                                    {{ \Carbon\Carbon::parse($transaction['time'])->diffForHumans() }}
                                </div>
                            </div>
                            <div class="text-white font-bold drop-shadow ml-2">
                                {{ number_format($transaction['amount'], 0) }} kr
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        
        <!-- Footer -->
        <div class="mt-2 pt-2 border-t border-white border-opacity-20 text-center">
            <p class="text-xs text-white text-opacity-70">
                Oppdateres hvert 5. minutt • Powered by Stripe 💳
            </p>
        </div>
    </div>
</div>
