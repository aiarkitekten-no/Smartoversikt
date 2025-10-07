@php
    // Determine mail status based on unread count
    $totalUnread = $data['total_unread'] ?? 0;
    
    if ($totalUnread === 0) {
        $mailStatus = 'clean'; // Green - Inbox Zero!
    } elseif ($totalUnread <= 10) {
        $mailStatus = 'normal'; // Blue - Normal
    } elseif ($totalUnread <= 50) {
        $mailStatus = 'busy'; // Yellow - Busy
    } else {
        $mailStatus = 'overflow'; // Red - Overflow!
    }
@endphp

<style>
    @keyframes envelope-float {
        0%, 100% { transform: translateY(0px) rotate(-2deg); }
        50% { transform: translateY(-10px) rotate(2deg); }
    }
    
    @keyframes mail-arrive {
        0% { transform: translateY(-100%) scale(0.5); opacity: 0; }
        50% { transform: translateY(0%) scale(1.1); opacity: 1; }
        100% { transform: translateY(0%) scale(1); opacity: 1; }
    }
    
    @keyframes notification-ping {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.3); opacity: 0.5; }
    }
    
    @keyframes sparkle {
        0%, 100% { opacity: 0; transform: scale(0) rotate(0deg); }
        50% { opacity: 1; transform: scale(1) rotate(180deg); }
    }
    
    @keyframes inbox-celebrate {
        0%, 100% { transform: scale(1) rotate(0deg); }
        25% { transform: scale(1.1) rotate(-10deg); }
        75% { transform: scale(1.1) rotate(10deg); }
    }
    
    .mail-clean {
        background: linear-gradient(135deg, #10B981 0%, #34D399 50%, #6EE7B7 100%);
    }
    
    .mail-normal {
        background: linear-gradient(135deg, #3B82F6 0%, #60A5FA 50%, #93C5FD 100%);
    }
    
    .mail-busy {
        background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 50%, #FCD34D 100%);
    }
    
    .mail-overflow {
        background: linear-gradient(135deg, #DC2626 0%, #EF4444 50%, #F87171 100%);
    }
    
    .envelope {
        animation: envelope-float 3s ease-in-out infinite;
    }
    
    .new-mail {
        animation: mail-arrive 1s ease-out;
    }
    
    .notification-badge {
        animation: notification-ping 2s ease-in-out infinite;
    }
    
    .sparkle-icon {
        animation: sparkle 2s ease-in-out infinite;
    }
    
    .inbox-zero-icon {
        animation: inbox-celebrate 2s ease-in-out infinite;
    }
</style>

<div class="h-full flex flex-col p-2 relative overflow-hidden rounded-lg mail-{{ $mailStatus }}">
    <!-- Floating Envelopes Background -->
    <div class="absolute inset-0 opacity-10 pointer-events-none">
        @for($i = 0; $i < 5; $i++)
            @php
                $top = rand(10, 80);
                $left = rand(10, 80);
                $delay = rand(0, 20) / 10;
                $size = rand(20, 40);
            @endphp
            <div class="envelope absolute" style="top: {{ $top }}%; left: {{ $left }}%; width: {{ $size }}px; height: {{ $size * 0.7 }}px; animation-delay: {{ $delay }}s;">
                <svg viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke="white" stroke-width="2" fill="none"/>
                </svg>
            </div>
        @endfor
    </div>
    
    <!-- Sparkles for Inbox Zero -->
    @if($mailStatus === 'clean')
        @for($i = 0; $i < 8; $i++)
            @php
                $top = rand(10, 90);
                $left = rand(10, 90);
                $delay = rand(0, 20) / 10;
            @endphp
            <div class="sparkle-icon absolute" style="top: {{ $top }}%; left: {{ $left }}%; animation-delay: {{ $delay }}s;">
                ✨
            </div>
        @endfor
    @endif
    
    <div class="relative z-0">
        @if(isset($data['status']) && $data['status'] === 'not_configured')
            <div class="text-center py-1">
                <div class="text-white text-opacity-90 mb-1 envelope">
                    <svg class="w-16 h-16 mx-auto drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-white drop-shadow mb-1">IMAP ikke konfigurert</p>
                <p class="text-xs text-white text-opacity-80 drop-shadow">Legg til innstillinger for å se e-poststatistikk</p>
                <a href="{{ route('settings.index') }}" class="mt-0.5 inline-block text-xs text-white font-semibold hover:text-opacity-80 hover:underline drop-shadow">
                    Konfigurer IMAP →
                </a>
            </div>
        @elseif(isset($data['error']))
            <div class="text-center text-white">
                <p class="text-xs font-semibold drop-shadow">Feil</p>
                <p class="text-xs mt-1 drop-shadow">{{ $data['error'] }}</p>
            </div>
        @else
            <!-- Status Header -->
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <span class="text-2xl {{ $mailStatus === 'clean' ? 'inbox-zero-icon' : '' }}">
                        @if($mailStatus === 'clean')
                            🎉
                        @elseif($mailStatus === 'normal')
                            📬
                        @elseif($mailStatus === 'busy')
                            📨
                        @else
                            📮
                        @endif
                    </span>
                    <div class="text-white drop-shadow-lg">
                        <h3 class="text-sm font-bold">E-post</h3>
                        @if($mailStatus === 'clean')
                            <p class="text-xs opacity-90">✨ Inbox Zero!</p>
                        @elseif($mailStatus === 'normal')
                            <p class="text-xs opacity-90">Alt under kontroll</p>
                        @elseif($mailStatus === 'busy')
                            <p class="text-xs opacity-90">⚠️ Travelt</p>
                        @else
                            <p class="text-xs opacity-90">🚨 Mange uleste!</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Totals -->
            <div class="grid grid-cols-2 gap-1 mb-0.5">
                <div class="text-center p-3 bg-white bg-opacity-20 backdrop-blur-sm rounded-lg">
                    <p class="text-base font-bold text-white drop-shadow">{{ $data['total_messages'] ?? 0 }}</p>
                    <p class="text-xs text-white text-opacity-80 mt-1">Total e-poster</p>
                </div>
                <div class="text-center p-3 bg-white bg-opacity-30 backdrop-blur-sm rounded-lg relative">
                    @if($totalUnread > 0)
                        <div class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center notification-badge">
                            <span class="text-xs font-bold text-white">!</span>
                        </div>
                    @endif
                    <p class="text-base font-bold text-white drop-shadow">{{ $data['total_unread'] ?? 0 }}</p>
                    <p class="text-xs text-white text-opacity-80 mt-1">Uleste</p>
                </div>
            </div>

            <!-- Accounts List -->
            @if(isset($data['accounts']) && count($data['accounts']) > 0)
                <div class="space-y-0.5 mb-1">
                    <p class="text-xs font-semibold text-white text-opacity-70 uppercase drop-shadow">Kontoer</p>
                    @foreach($data['accounts'] as $account)
                        <div class="flex items-center justify-between p-2 bg-white bg-opacity-20 backdrop-blur-sm rounded border border-white border-opacity-30">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-white drop-shadow truncate">{{ $account['name'] }}</p>
                                @if(isset($account['error']))
                                    <p class="text-xs text-red-200 drop-shadow">{{ $account['error'] }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-1 ml-3">
                                <div class="text-right">
                                    <p class="text-xs text-white text-opacity-70">Total</p>
                                    <p class="text-xs font-semibold text-white drop-shadow">{{ $account['total_messages'] ?? 0 }}</p>
                                </div>
                                @if(($account['unread'] ?? 0) > 0)
                                    <div class="text-right bg-yellow-400 bg-opacity-30 px-2 py-1 rounded">
                                        <p class="text-xs text-white text-opacity-70">Uleste</p>
                                        <p class="text-xs font-bold text-white drop-shadow">{{ $account['unread'] }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</div>
