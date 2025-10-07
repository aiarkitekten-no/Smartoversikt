<style>
    @keyframes news-pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.85; }
    }
    
    @keyframes breaking-flash {
        0%, 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        50% { box-shadow: 0 0 0 4px rgba(34, 197, 94, 0); }
    }
    
    @keyframes slide-in {
        from { transform: translateX(-10px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    .news-fresh {
        background: linear-gradient(135deg, #10B981 0%, #34D399 100%);
        border-left: 3px solid #059669;
    }
    
    .news-recent {
        background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%);
        border-left: 3px solid #D97706;
    }
    
    .news-old {
        background: linear-gradient(135deg, #EF4444 0%, #F87171 100%);
        border-left: 3px solid #DC2626;
    }
    
    .news-item {
        animation: slide-in 0.3s ease-out forwards;
    }
    
    .news-breaking {
        animation: breaking-flash 2s ease-in-out infinite;
    }
    
    .age-badge {
        font-size: 9px;
        padding: 2px 6px;
        border-radius: 9999px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .age-badge-fresh {
        background: rgba(255, 255, 255, 0.9);
        color: #059669;
        box-shadow: 0 0 8px rgba(34, 197, 94, 0.3);
    }
    
    .age-badge-recent {
        background: rgba(255, 255, 255, 0.9);
        color: #D97706;
    }
    
    .age-badge-old {
        background: rgba(255, 255, 255, 0.9);
        color: #DC2626;
    }
    
    .news-item:hover {
        transform: translateX(4px);
        transition: transform 0.2s ease;
    }
</style>

<div class="h-full flex flex-col p-2">
    @if(isset($data['error']))
        <div class="text-center text-gray-500">
            <p class="text-xs">{{ $data['error'] }}</p>
        </div>
    @else
        @if(isset($data['items']) && count($data['items']) > 0)
            @php
                // Count articles by age
                $freshCount = 0;
                $recentCount = 0;
                $oldCount = 0;
                $now = \Carbon\Carbon::now();
                
                foreach($data['items'] as $item) {
                    $articleDate = \Carbon\Carbon::parse($item['date']);
                    $hoursOld = $now->diffInHours($articleDate);
                    
                    if ($hoursOld < 2) {
                        $freshCount++;
                    } elseif ($hoursOld < 8) {
                        $recentCount++;
                    } else {
                        $oldCount++;
                    }
                }
            @endphp
            
            <!-- News Status Header -->
            <div class="mb-2 flex items-center justify-between bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-2">
                <div class="flex items-center gap-2">
                    <span class="text-lg">📰</span>
                    <div>
                        <h3 class="text-xs font-bold text-gray-800">Nyheter</h3>
                        <div class="flex items-center gap-1 text-xs">
                            @if($freshCount > 0)
                                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full bg-green-100 text-green-700">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                                    {{ $freshCount }}
                                </span>
                            @endif
                            @if($recentCount > 0)
                                <span class="px-1.5 py-0.5 rounded-full bg-yellow-100 text-yellow-700">{{ $recentCount }}</span>
                            @endif
                            @if($oldCount > 0)
                                <span class="px-1.5 py-0.5 rounded-full bg-red-100 text-red-700">{{ $oldCount }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Legend -->
                <div class="flex flex-col gap-0.5 text-xs">
                    <div class="flex items-center gap-1">
                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                        <span class="text-gray-600">< 2t</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                        <span class="text-gray-600">2-8t</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                        <span class="text-gray-600">> 8t</span>
                    </div>
                </div>
            </div>
            
            <div class="space-y-1 max-h-80 overflow-y-auto">
                @foreach($data['items'] as $index => $item)
                    @php
                        $articleDate = \Carbon\Carbon::parse($item['date']);
                        $hoursOld = $now->diffInHours($articleDate);
                        $minutesOld = $now->diffInMinutes($articleDate);
                        
                        // Round minutes to nearest 5
                        $roundedMinutes = round($minutesOld / 5) * 5;
                        if ($roundedMinutes == 0) $roundedMinutes = 1;
                        
                        // Determine freshness category
                        if ($hoursOld < 2) {
                            $ageClass = 'news-fresh';
                            $badgeClass = 'age-badge-fresh';
                            if ($minutesOld < 60) {
                                $ageLabel = $roundedMinutes . ' min';
                            } else {
                                $ageLabel = '< 2t';
                            }
                            $isBreaking = $minutesOld < 30;
                        } elseif ($hoursOld < 8) {
                            $ageClass = 'news-recent';
                            $badgeClass = 'age-badge-recent';
                            $ageLabel = $hoursOld . 't';
                            $isBreaking = false;
                        } else {
                            $ageClass = 'news-old';
                            $badgeClass = 'age-badge-old';
                            if ($articleDate->isToday()) {
                                $ageLabel = 'I dag';
                            } elseif ($articleDate->isYesterday()) {
                                $ageLabel = 'I går';
                            } else {
                                $ageLabel = $articleDate->diffInDays($now) . 'd';
                            }
                            $isBreaking = false;
                        }
                    @endphp
                    
                    <a href="{{ $item['link'] }}" target="_blank" 
                       class="block rounded-lg transition-all group news-item {{ $ageClass }} {{ $isBreaking ? 'news-breaking' : '' }}"
                       style="animation-delay: {{ $index * 0.05 }}s;">
                        <div class="p-2 relative">
                            <!-- Age Badge -->
                            <div class="absolute top-1 right-1 age-badge {{ $badgeClass }}">
                                @if($isBreaking)
                                    🔥 {{ $ageLabel }}
                                @else
                                    {{ $ageLabel }}
                                @endif
                            </div>
                            
                            <div class="flex items-start gap-2">
                                @if(($data['show_images'] ?? false) && !empty($item['image']))
                                    <img src="{{ $item['image'] }}" 
                                         alt="{{ $item['title'] }}"
                                         class="w-16 h-12 object-cover rounded flex-shrink-0 shadow-md"
                                         loading="lazy"
                                         onerror="this.style.display='none'">
                                @else
                                    <div class="w-8 h-8 flex items-center justify-center bg-white bg-opacity-50 rounded-full flex-shrink-0">
                                        <span class="text-base">�</span>
                                    </div>
                                @endif
                                
                                <div class="flex-1 min-w-0 pr-12">
                                    <h4 class="text-xs font-bold text-white drop-shadow-lg leading-tight line-clamp-2 group-hover:underline">
                                        {{ $item['title'] }}
                                    </h4>
                                    
                                    @if(($data['show_descriptions'] ?? true) && !empty($item['description']))
                                        <p class="text-xs text-white text-opacity-90 line-clamp-1 mt-1 drop-shadow">
                                            {{ $item['description'] }}
                                        </p>
                                    @endif
                                    
                                    <div class="flex items-center gap-1.5 text-xs text-white text-opacity-80 mt-1 drop-shadow">
                                        @if($data['show_source'] ?? true)
                                            <span class="font-semibold">{{ $item['source'] }}</span>
                                            <span>•</span>
                                        @endif
                                        <span>{{ $articleDate->format('H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            
            <div class="mt-2 pt-2 border-t border-gray-200">
                <div class="flex items-center justify-between text-xs text-gray-600">
                    <div class="flex items-center gap-1">
                        <span class="font-semibold">{{ count($data['items']) }}</span>
                        <span>artikler fra</span>
                        <span class="font-semibold">{{ $data['total_feeds'] }}</span>
                        <span>kilder</span>
                    </div>
                    
                    @if(isset($data['display_mode']))
                        <span class="px-2 py-0.5 bg-gray-100 rounded-full text-xs">
                            @if($data['display_mode'] === 'grouped')
                                📑 Gruppert
                            @elseif($data['display_mode'] === 'latest_per_source')
                                ⭐ Siste fra hver
                            @else
                                🔀 Blandet
                            @endif
                        </span>
                    @endif
                </div>
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <span class="text-4xl mb-2 block">📭</span>
                <p class="text-xs">Ingen nyheter tilgjengelig</p>
            </div>
        @endif
    @endif
</div>
