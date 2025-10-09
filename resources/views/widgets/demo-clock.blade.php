@php
    $currentHour = (int) date('H');
    $currentMinute = (int) date('i');
    $timeValue = $currentHour * 100 + $currentMinute; // e.g., 15:30 = 1530
    
    // Determine wife mood level based on time
    if ($timeValue >= 1530 && $timeValue < 1600) {
        $wifeMood = 'calm'; // 15:30-16:00 - Rolig
    } elseif ($timeValue >= 1600 && $timeValue < 1630) {
        $wifeMood = 'impatient'; // 16:00-16:30 - Utålmodig
    } elseif ($timeValue >= 1630 && $timeValue < 1700) {
        $wifeMood = 'worried'; // 16:30-17:00 - Bekymret
    } elseif ($timeValue >= 1700 && $timeValue < 1800) {
        $wifeMood = 'irritated'; // 17:00-18:00 - Irritert
    } elseif ($timeValue >= 1800 && $timeValue < 1830) {
        $wifeMood = 'furious'; // 18:00-18:30 - Rasende
    } else {
        $wifeMood = null; // Normal time
    }
    
    // Determine time of day for background
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
    
    @keyframes pulse-glow {
        0%, 100% { box-shadow: 0 0 10px rgba(255, 0, 0, 0.5); }
        50% { box-shadow: 0 0 20px rgba(255, 0, 0, 0.8); }
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-2px); }
        75% { transform: translateX(2px); }
    }
    
    @keyframes stomp {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(2px); }
    }
    
    @keyframes heart-float {
        0% { transform: translateY(0) scale(1); opacity: 1; }
        100% { transform: translateY(-100px) scale(0.5); opacity: 0; }
    }
    
    @keyframes flame-rise {
        0% { transform: translateY(0) scale(1); opacity: 0.8; }
        100% { transform: translateY(-50px) scale(0.5); opacity: 0; }
    }
    
    @keyframes flower-wilt {
        0% { transform: rotate(0deg); opacity: 1; }
        100% { transform: rotate(15deg); opacity: 0.3; }
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
    
    /* Wife Mood Backgrounds */
    .time-bg-calm {
        background: linear-gradient(135deg, #FFC0CB 0%, #FFB6C1 50%, #FFE4E1 100%);
        background-size: 200% 200%;
        animation: dawn-gradient 8s ease infinite;
    }
    
    .time-bg-impatient {
        background: linear-gradient(135deg, #FFB88C 0%, #FFA07A 50%, #FF8C69 100%);
        background-size: 200% 200%;
        animation: dawn-gradient 6s ease infinite;
    }
    
    .time-bg-worried {
        background: linear-gradient(135deg, #FF8C42 0%, #FF7F50 50%, #FF6347 100%);
        animation: shake 2s ease-in-out infinite;
    }
    
    .time-bg-irritated {
        background: linear-gradient(135deg, #DC143C 0%, #B22222 50%, #8B0000 100%);
        animation: stomp 1s ease-in-out infinite;
    }
    
    .time-bg-furious {
        background: linear-gradient(135deg, #8B0000 0%, #4B0000 50%, #1a0000 100%);
        animation: pulse-glow 1.5s ease-in-out infinite, stomp 0.5s ease-in-out infinite;
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
    
    .heart-particle {
        position: absolute;
        font-size: 20px;
        animation: heart-float 3s ease-in-out infinite;
    }
    
    .flame-particle {
        position: absolute;
        font-size: 24px;
        animation: flame-rise 2s ease-in-out infinite;
    }
    
    .flower-icon {
        animation: flower-wilt 3s ease-in-out forwards;
    }
@php
    $sweetMessages = [
        'Hei kjære! Tenker på deg. Kommer snart hjem!',
        'Du er verdens beste! Blir ikke seint i dag, love!',
        'Savner deg allerede! På vei hjem nå!',
        'Du er min stjerne. Gleder meg til å se deg!',
        'Beklager forsinkelsen! Kompenserer med klem når jeg kommer!',
        'Takk for at du er så tålmodig! Du er gull verdt!',
        'Vet du er den beste kona i verden? Kommer fort!',
        'Skal vi bestille middag? Min treat!',
        'Du gjør hver dag bedre! Snart hjemme!',
        'Elsker deg mest! Bringer dessert med hjem!',
        'Verden er bedre med deg i den! Snart der!',
        'Du er min superhelt! Takk for alt!',
        'Livet med deg er perfekt! Kommer om litt!',
        'Du fortjener verden! Gleder meg til kveldsmat sammen!',
        'Ingen klemmer som dine! Lengter hjem!',
        'Du gjør meg til et bedre menneske! På vei!',
        'Skal nok ikke bli seint! Love, love!',
        'Du er min favoritt person! Kommer snart!',
        'Takk for at du venter! Du er best!',
        'Kan ikke vente med å se deg! Nesten hjemme!',
        'Du lyser opp livet mitt! Snart sammen igjen!',
        'Er så heldig som har deg! Kommer fort!',
        'Du er min drøm som ble virkelighet! På vei hjem!',
        'Ingen som deg i hele verden! Snart der!',
        'Takk for at du er deg! Gleder meg!',
        'Du er min solskinn! Kommer snart hjem til deg!',
        'Er så glad i deg! Blir ikke lenge nå!',
        'Du er perfekt som du er! Nesten hjemme!',
        'Skal ta med noe godt hjem! Surprise!',
        'Du er min alt! Kommer om kort tid!'
    ];
@endphp

</style>

<div class="h-full flex flex-col rounded-lg shadow-sm p-6 relative overflow-hidden time-bg-{{ $wifeMood ?? $timeOfDay }}" 
     x-data="{
         ...widgetData('{{ $widget->key ?? 'demo.clock' }}'),
         wifeMood: '{{ $wifeMood }}',
         sendingSms: false,
         smsStatus: null,
         messageIndex: 0,
         sweetMessages: @js($sweetMessages),
         
         async sendApologySms() {
             if (this.sendingSms) return;
             
             this.sendingSms = true;
             this.smsStatus = { sending: true, message: 'Sender søt melding...' };
             
             const message = this.sweetMessages[this.messageIndex];
             this.messageIndex = (this.messageIndex + 1) % this.sweetMessages.length;
             
             try {
                 const response = await fetch('/api/sms/send', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                     },
                     body: JSON.stringify({
                         to: '4747487778',
                         message: message
                     })
                 });
                 
                 const result = await response.json();
                 
                 if (result.success) {
                     this.smsStatus = { 
                         success: true, 
                         message: 'Sendt! ' + message.substring(0, 30) + '...' 
                     };
                 } else {
                     this.smsStatus = { 
                         success: false, 
                         message: 'Feil: ' + (result.error || 'Ukjent feil')
                     };
                 }
                 
                 setTimeout(() => { this.smsStatus = null; }, 5000);
                 
             } catch (error) {
                 this.smsStatus = { 
                     success: false, 
                     message: 'Nettverksfeil: ' + error.message 
                 };
                 setTimeout(() => { this.smsStatus = null; }, 5000);
             } finally {
                 this.sendingSms = false;
             }
         }
     }" 
     x-init="startRefresh(10)">
    
    <!-- Wife Mood Decorations -->
    @if($wifeMood === 'calm')
        <!-- Floating hearts -->
        @for($i = 0; $i < 5; $i++)
            @php
                $left = rand(10, 80);
                $delay = rand(0, 30) / 10;
            @endphp
            <div class="heart-particle" style="left: {{ $left }}%; bottom: 0; animation-delay: {{ $delay }}s;">💕</div>
        @endfor
    @elseif($wifeMood === 'impatient')
        <!-- Clock icon pulsing -->
        <div class="absolute top-4 right-4 text-4xl opacity-70 animate-pulse">⏰</div>
    @elseif($wifeMood === 'worried')
        <!-- Phone icon shaking -->
        <div class="absolute top-4 right-4 text-4xl opacity-70" style="animation: shake 0.5s ease-in-out infinite;">📱</div>
    @elseif($wifeMood === 'irritated')
        <!-- Wilting flowers + flame particles -->
        <div class="absolute top-4 right-4 text-4xl flower-icon">🥀</div>
        @for($i = 0; $i < 4; $i++)
            @php
                $left = rand(10, 80);
                $delay = rand(0, 20) / 10;
            @endphp
            <div class="flame-particle" style="left: {{ $left }}%; bottom: 0; animation-delay: {{ $delay }}s;">🔥</div>
        @endfor
    @elseif($wifeMood === 'furious')
        <!-- Devil + flames everywhere -->
        <div class="absolute top-4 right-4 text-5xl opacity-90 animate-pulse">😈</div>
        @for($i = 0; $i < 8; $i++)
            @php
                $left = rand(5, 90);
                $delay = rand(0, 30) / 10;
            @endphp
            <div class="flame-particle" style="left: {{ $left }}%; bottom: 0; animation-delay: {{ $delay }}s;">🔥</div>
        @endfor
    @else
        <!-- Normal Time of Day Decorations -->
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
            <div class="text-lg font-bold {{ ($wifeMood && in_array($wifeMood, ['irritated', 'furious'])) ? 'text-white' : (($timeOfDay === 'night' || $wifeMood) ? 'text-yellow-300' : 'text-indigo-600') }}" x-text="data?.time || '--:--:--'"></div>
            <div class="text-xs {{ ($wifeMood && in_array($wifeMood, ['irritated', 'furious'])) ? 'text-gray-200' : (($timeOfDay === 'night' || $wifeMood) ? 'text-gray-300' : 'text-gray-600') }} mt-0.5" x-text="data?.date || 'Laster...'"></div>
        </div>

        @if($wifeMood)
            <!-- Wife Mood Status -->
            <div class="my-2 p-2 bg-white bg-opacity-20 backdrop-blur-sm rounded text-center relative z-10">
                @if($wifeMood === 'calm')
                    <div class="text-sm font-semibold text-white">😊 Alt er bra 💖</div>
                @elseif($wifeMood === 'impatient')
                    <div class="text-sm font-semibold text-white">🤔 Husker du noe i dag? ⏰</div>
                @elseif($wifeMood === 'worried')
                    <div class="text-sm font-semibold text-white">😐 HVOR ER DU??? 📱</div>
                @elseif($wifeMood === 'irritated')
                    <div class="text-sm font-semibold text-white">😤 Blomster hadde vært fint... 💐</div>
                @elseif($wifeMood === 'furious')
                    <div class="text-sm font-semibold text-white">🔥 Sofaen er klar for deg 🛋️😈</div>
                @endif
            </div>

            <!-- Apology SMS Button (shows from 17:00) -->
            @if(in_array($wifeMood, ['irritated', 'furious']))
                <div class="my-2 relative z-10">
                    <button 
                        @click="sendApologySms()"
                        :disabled="sendingSms"
                        class="w-full py-2 px-3 rounded-lg font-semibold text-sm transition-all transform hover:scale-105"
                        :class="sendingSms ? 'bg-gray-400 cursor-not-allowed' : 'bg-pink-600 hover:bg-pink-500 text-white shadow-lg'"
                    >
                        <span x-show="!sendingSms">💌 Send Unnskyldning SMS</span>
                        <span x-show="sendingSms">⏳ Sender...</span>
                    </button>
                    
                    <!-- SMS Status -->
                    <div x-show="smsStatus" x-cloak class="mt-2 p-2 rounded text-xs text-center"
                         :class="smsStatus?.success ? 'bg-green-500 bg-opacity-80 text-white' : (smsStatus?.sending ? 'bg-blue-500 bg-opacity-80 text-white' : 'bg-red-500 bg-opacity-80 text-white')">
                        <span x-show="smsStatus?.success">✅</span>
                        <span x-show="smsStatus?.success === false">❌</span>
                        <span x-text="smsStatus?.message"></span>
                    </div>
                </div>
            @endif
        @endif

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

        @if($wifeMood)
            @php
                // Daily rotating story panel - changes based on day of year
                $dayOfYear = date('z');
                $storyPanels = [
                    // 1. Progressiv Countdown Bar
                    1 => function($wifeMood, $currentHour, $currentMinute) {
                        $waitMinutes = max(0, ($currentHour * 60 + $currentMinute) - (15 * 60 + 30));
                        $barWidth = min(100, ($waitMinutes / 180) * 100); // Max 3 hours = 100%
                        $color = $barWidth < 30 ? 'bg-green-500' : ($barWidth < 60 ? 'bg-yellow-500' : 'bg-red-500');
                        return '<div class="text-xs"><div class="font-semibold mb-1">⏳ Tonje venter:</div><div class="w-full bg-gray-300 rounded-full h-2"><div class="'.$color.' h-2 rounded-full transition-all" style="width: '.$barWidth.'%"></div></div><div class="text-[10px] mt-1">'.$waitMinutes.' minutter</div></div>';
                    },
                    // 2. Emoji Timeline
                    2 => function($wifeMood, $currentHour, $currentMinute) {
                        $timeline = [
                            ['15:30', '😊'],
                            ['16:00', '🤔'],
                            ['16:30', '😐'],
                            ['17:00', '😤'],
                            ['18:00', '😈']
                        ];
                        $html = '<div class="flex justify-around text-center">';
                        foreach($timeline as $item) {
                            $html .= '<div class="text-xs"><div class="text-lg">'.$item[1].'</div><div class="text-[9px]">'.$item[0].'</div></div>';
                        }
                        $html .= '</div>';
                        return $html;
                    },
                    // 3. Live Updates Feed
                    3 => function($wifeMood, $currentHour, $currentMinute) {
                        $messages = [
                            'calm' => '💭 "Han kommer snart 💕"',
                            'impatient' => '💭 "Hmm... hvor blir han av? 🤔"',
                            'worried' => '💭 "Begynner å bli bekymret... 😐"',
                            'irritated' => '💭 "HVOR ER HAN?! 😤"',
                            'furious' => '💭 "Sofaen venter... 😈"'
                        ];
                        return '<div class="text-xs italic text-center">'.$messages[$wifeMood].'</div>';
                    },
                    // 5. Blomster-Meter
                    5 => function($wifeMood, $currentHour, $currentMinute) {
                        $flowers = [
                            'calm' => '🌹',
                            'impatient' => '🌹🌹',
                            'worried' => '🌹🌹🌹',
                            'irritated' => '🌹🌹🌹🌹🌹',
                            'furious' => '💐💐💐'
                        ];
                        return '<div class="text-xs text-center"><div class="font-semibold">Blomster trengs:</div><div class="text-lg">'.$flowers[$wifeMood].'</div></div>';
                    },
                    // 7. Temperatur-Gauge
                    7 => function($wifeMood, $currentHour, $currentMinute) {
                        $temps = [
                            'calm' => ['20%', '🌡️ Rolig'],
                            'impatient' => ['40%', '🌡️ Varm'],
                            'worried' => ['60%', '🌡️ Het'],
                            'irritated' => ['80%', '🌡️ Kokende'],
                            'furious' => ['100%', '🌡️ LAVA!']
                        ];
                        $temp = $temps[$wifeMood];
                        return '<div class="text-xs"><div class="font-semibold mb-1">'.$temp[1].'</div><div class="w-full bg-gray-300 rounded-full h-2"><div class="bg-gradient-to-r from-blue-500 via-yellow-500 to-red-600 h-2 rounded-full" style="width: '.$temp[0].'"></div></div></div>';
                    },
                    // 9. Tapping Foot
                    9 => function($wifeMood, $currentHour, $currentMinute) {
                        $speed = [
                            'calm' => 'animate-none',
                            'impatient' => 'animate-pulse',
                            'worried' => 'animate-bounce',
                            'irritated' => 'animate-ping',
                            'furious' => 'animate-ping'
                        ];
                        return '<div class="text-center"><div class="text-2xl '.$speed[$wifeMood].'">👠</div><div class="text-[10px]">*tap tap tap*</div></div>';
                    },
                    // 10. Clock Watching Counter
                    10 => function($wifeMood, $currentHour, $currentMinute) {
                        $waitMinutes = max(0, ($currentHour * 60 + $currentMinute) - (15 * 60 + 30));
                        $checks = min(99, $waitMinutes * 2); // 2 checks per minute
                        return '<div class="text-xs text-center"><div class="text-lg font-bold">'.$checks.'x</div><div>Tonje har sjekket klokka</div></div>';
                    },
                    // 11. Phone Check Tracker
                    11 => function($wifeMood, $currentHour, $currentMinute) {
                        $waitMinutes = max(0, ($currentHour * 60 + $currentMinute) - (15 * 60 + 30));
                        $phoneChecks = min(99, floor($waitMinutes * 1.5));
                        $calls = min(9, floor($waitMinutes / 30));
                        return '<div class="text-xs grid grid-cols-2 gap-1"><div>📱 Sjekket: '.$phoneChecks.'x</div><div>☎️ Ring: '.$calls.'x</div></div>';
                    },
                    // 12. Food Status
                    12 => function($wifeMood, $currentHour, $currentMinute) {
                        $status = [
                            'calm' => '🍝 Middag: 🔥 Varmes opp',
                            'impatient' => '🍝 Middag: 🌡️ Varm',
                            'worried' => '🍝 Middag: 🌡️ Lunken',
                            'irritated' => '🍝 Middag: ❄️ Kald',
                            'furious' => '🍝 Middag: 🗑️ I søpla'
                        ];
                        return '<div class="text-xs text-center">'.$status[$wifeMood].'</div>';
                    },
                    // 14. Flower Shop Alert
                    14 => function($wifeMood, $currentHour, $currentMinute) {
                        $minsUntilClose = max(0, (18 * 60) - ($currentHour * 60 + $currentMinute));
                        if ($minsUntilClose > 0) {
                            return '<div class="text-xs text-center"><div class="font-semibold">🌹 Blomsterbutikk</div><div class="text-red-600">⏰ Stenger om '.$minsUntilClose.' min!</div></div>';
                        }
                        return '<div class="text-xs text-center text-red-600">🌹 Blomsterbutikken er STENGT! 😱</div>';
                    },
                    // 15. Live Quotes
                    15 => function($wifeMood, $currentHour, $currentMinute) {
                        $quotes = [
                            'calm' => '"Har du glemt noe?" 🤨',
                            'impatient' => '"Trafikken kan da ikke være SÅ ille..." 🙄',
                            'worried' => '"Hvorfor svarer du ikke?!" 😰',
                            'irritated' => '"Blomster? BLOMSTER?!" 😤',
                            'furious' => '"Sofaen er myk i år!" 😈'
                        ];
                        return '<div class="text-xs italic text-center"><div class="font-semibold mb-1">Tonje sier:</div>"'.$quotes[$wifeMood].'"</div>';
                    },
                    // 16. Sarkastisk Tips
                    16 => function($wifeMood, $currentHour, $currentMinute) {
                        $tips = [
                            'calm' => '💡 Protip: Alt er fortsatt bra',
                            'impatient' => '💡 Protip: En melding hadde vært greit',
                            'worried' => '💡 Protip: Blomster selges hos Rema',
                            'irritated' => '💡 Protip: "Beklager" er et godt ord',
                            'furious' => '💡 Protip: Sofaen har WiFi'
                        ];
                        return '<div class="text-xs text-center">'.$tips[$wifeMood].'</div>';
                    },
                    // 17. Forventet hjemkomst Counter
                    17 => function($wifeMood, $currentHour, $currentMinute) {
                        $expected = '16:00';
                        $now = sprintf('%02d:%02d', $currentHour, $currentMinute);
                        $delayMins = max(0, ($currentHour * 60 + $currentMinute) - (16 * 60));
                        if ($delayMins > 0) {
                            $hours = floor($delayMins / 60);
                            $mins = $delayMins % 60;
                            $delay = ($hours > 0 ? $hours.'t ' : '') . $mins.'min';
                            return '<div class="text-xs"><div>Du sa: '.$expected.'</div><div>Klokka: '.$now.'</div><div class="text-red-600 font-semibold">⚠️ '.$delay.' FORSINKET</div></div>';
                        }
                        return '<div class="text-xs text-center text-green-600">✅ I rute!</div>';
                    },
                ];
                
                // Select panel based on day (rotates through available panels)
                $availablePanels = array_keys($storyPanels);
                $selectedPanelKey = $availablePanels[$dayOfYear % count($availablePanels)];
                $selectedPanel = $storyPanels[$selectedPanelKey];
                
                $panelHtml = $selectedPanel($wifeMood, (int)date('H'), (int)date('i'));
            @endphp
            
            <!-- Daily Story Panel -->
            <div class="mt-2 p-3 bg-white bg-opacity-20 backdrop-blur-sm rounded-lg relative z-10 border border-white border-opacity-30">
                {!! $panelHtml !!}
            </div>
        @endif
    </div>

    <!-- Loading State -->
    <div x-show="loading && !data" class="text-center py-1">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        <p class="text-xs text-gray-500 mt-0.5">Laster...</p>
    </div>
</div>
