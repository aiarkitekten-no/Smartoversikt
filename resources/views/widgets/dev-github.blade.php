@php
    $activityStatus = $data['activity_status'] ?? 'quiet';
    $todayCommits = $data['today']['commits'] ?? 0;
    $weekCommits = $data['this_week']['commits'] ?? 0;
    
    // Get username from settings or data
    $username = $userWidget->settings['username'] ?? $data['username'] ?? 'octocat';
    
    // Determine status styling
    if ($activityStatus === 'very_active') {
        $statusClass = 'status-very-active';
        $statusIcon = '🔥';
        $statusText = 'On Fire!';
    } elseif ($activityStatus === 'active') {
        $statusClass = 'status-active';
        $statusIcon = '💪';
        $statusText = 'Productive Day';
    } elseif ($activityStatus === 'moderate') {
        $statusClass = 'status-moderate';
        $statusIcon = '👨‍💻';
        $statusText = 'Getting Work Done';
    } else {
        $statusClass = 'status-quiet';
        $statusIcon = '😴';
        $statusText = 'Quiet Day';
    }
    
    $languageColors = [
        'PHP' => '#777BB4',
        'JavaScript' => '#F7DF1E',
        'TypeScript' => '#3178C6',
        'Python' => '#3776AB',
        'Ruby' => '#CC342D',
    ];
@endphp

<style>
    @keyframes code-stream {
        0% { transform: translateY(-100%); opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 1; }
        100% { transform: translateY(100%); opacity: 0; }
    }
    
    @keyframes commit-particle {
        0% { transform: translate(0, 0) scale(0); opacity: 0; }
        10% { opacity: 1; }
        50% { transform: translate(var(--x), var(--y)) scale(1); opacity: 0.8; }
        100% { transform: translate(calc(var(--x) * 2), calc(var(--y) * 2)) scale(0); opacity: 0; }
    }
    
    @keyframes git-graph-draw {
        from { stroke-dashoffset: 500; }
        to { stroke-dashoffset: 0; }
    }
    
    @keyframes octocat-wave {
        0%, 100% { transform: rotate(-10deg); }
        50% { transform: rotate(10deg); }
    }
    
    @keyframes contribution-pop {
        0% { transform: scale(0); opacity: 0; }
        50% { transform: scale(1.2); opacity: 1; }
        100% { transform: scale(1); opacity: 1; }
    }
    
    @keyframes flame-flicker {
        0%, 100% { transform: scale(1) translateY(0); opacity: 1; }
        50% { transform: scale(1.1) translateY(-5px); opacity: 0.8; }
    }
    
    @keyframes pr-merge {
        0% { transform: translateX(-100%) rotate(-10deg); opacity: 0; }
        50% { transform: translateX(0%) rotate(0deg); opacity: 1; }
        100% { transform: translateX(100%) rotate(10deg); opacity: 0; }
    }
    
    @keyframes stat-count-up {
        from { transform: scale(0.8); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    
    .status-very-active {
        background: linear-gradient(135deg, #DC2626 0%, #F59E0B 50%, #FCD34D 100%);
    }
    
    .status-active {
        background: linear-gradient(135deg, #059669 0%, #10B981 50%, #34D399 100%);
    }
    
    .status-moderate {
        background: linear-gradient(135deg, #0EA5E9 0%, #38BDF8 50%, #7DD3FC 100%);
    }
    
    .status-quiet {
        background: linear-gradient(135deg, #4B5563 0%, #6B7280 50%, #9CA3AF 100%);
    }
    
    .code-line {
        position: absolute;
        font-family: 'Courier New', monospace;
        font-size: 10px;
        color: rgba(255,255,255,0.3);
        white-space: nowrap;
        animation: code-stream 5s linear infinite;
    }
    
    .commit-dot {
        position: absolute;
        width: 8px;
        height: 8px;
        background: white;
        border-radius: 50%;
        animation: commit-particle 2s ease-out infinite;
    }
    
    .git-graph-line {
        stroke-dasharray: 500;
        animation: git-graph-draw 3s ease-out forwards;
    }
    
    .octocat-icon {
        animation: octocat-wave 2s ease-in-out infinite;
        transform-origin: bottom center;
    }
    
    .flame-icon {
        animation: flame-flicker 1s ease-in-out infinite;
    }
    
    .contribution-square {
        animation: contribution-pop 0.5s ease-out forwards;
    }
    
    .pr-indicator {
        animation: pr-merge 3s ease-in-out infinite;
    }
    
    .stat-number {
        animation: stat-count-up 0.6s ease-out forwards;
    }
    
    .commit-item {
        animation: contribution-pop 0.4s ease-out forwards;
    }
</style>

<div 
    x-data="widgetData('{{ $widget->key ?? 'dev.github' }}')" 
    x-init="init()"
    class="h-full flex flex-col overflow-hidden shadow-sm sm:rounded-lg relative {{ $statusClass }}"
>
    <!-- Code Stream Background -->
    @if($activityStatus === 'very_active' || $activityStatus === 'active')
        @php
            $codeSnippets = [
                'function commit() {',
                'git push origin main',
                'npm run build',
                'composer install',
                'const data = await fetch()',
                'public function store()',
                'return view("dashboard")',
                '// TODO: optimize',
            ];
        @endphp
        @for($i = 0; $i < 8; $i++)
            @php
                $left = rand(0, 100);
                $delay = rand(0, 50) / 10;
                $duration = rand(40, 60) / 10;
            @endphp
            <div class="code-line" style="left: {{ $left }}%; top: -10%; animation-delay: {{ $delay }}s; animation-duration: {{ $duration }}s;">
                {{ $codeSnippets[array_rand($codeSnippets)] }}
            </div>
        @endfor
    @endif
    
    <!-- Commit Particles (when very active) -->
    @if($activityStatus === 'very_active')
        @for($i = 0; $i < 20; $i++)
            @php
                $left = rand(10, 90);
                $top = rand(10, 90);
                $delay = rand(0, 20) / 10;
                $xMove = rand(-50, 50);
                $yMove = rand(-50, 50);
            @endphp
            <div class="commit-dot" style="left: {{ $left }}%; top: {{ $top }}%; --x: {{ $xMove }}px; --y: {{ $yMove }}px; animation-delay: {{ $delay }}s;"></div>
        @endfor
    @endif
    
    <!-- Git Graph SVG Background -->
    <svg class="absolute inset-0 w-full h-full opacity-10" viewBox="0 0 200 200" style="z-index: 1;">
        <path class="git-graph-line" d="M 20,100 Q 50,50 80,100 T 140,100 L 180,100" 
              stroke="white" stroke-width="2" fill="none"/>
        <circle cx="20" cy="100" r="5" fill="white"/>
        <circle cx="80" cy="100" r="5" fill="white"/>
        <circle cx="140" cy="100" r="5" fill="white"/>
        <circle cx="180" cy="100" r="5" fill="white"/>
    </svg>
    
    <div class="p-2 relative z-0">
        <!-- Header -->
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                <span class="text-2xl {{ $activityStatus === 'very_active' ? 'flame-icon' : 'octocat-icon' }}">
                    {{ $statusIcon }}
                </span>
                <div>
                    <h3 class="text-sm font-bold text-white drop-shadow-lg">GitHub Activity</h3>
                    <p class="text-xs text-white text-opacity-90 drop-shadow">{{ $statusText }}</p>
                </div>
            </div>
        </div>
        
        <!-- Today's Stats -->
        <div class="grid grid-cols-3 gap-1 mb-2">
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-2 text-center">
                <div class="text-2xl font-bold text-white drop-shadow stat-number">{{ $todayCommits }}</div>
                <div class="text-xs text-white text-opacity-80">Commits</div>
            </div>
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-2 text-center">
                <div class="text-2xl font-bold text-emerald-100 drop-shadow stat-number" style="animation-delay: 0.1s;">
                    +{{ $data['today']['lines_added'] ?? 0 }}
                </div>
                <div class="text-xs text-white text-opacity-80">Lines</div>
            </div>
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-2 text-center">
                <div class="text-2xl font-bold text-red-100 drop-shadow stat-number" style="animation-delay: 0.2s;">
                    -{{ $data['today']['lines_deleted'] ?? 0 }}
                </div>
                <div class="text-xs text-white text-opacity-80">Deleted</div>
            </div>
        </div>
        
        <!-- Week Stats -->
        <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-2 mb-2">
            <div class="text-xs text-white text-opacity-80 font-semibold mb-1">📊 This Week</div>
            <div class="grid grid-cols-2 gap-2 text-xs text-white">
                <div class="flex justify-between">
                    <span>Commits:</span>
                    <span class="font-bold">{{ $weekCommits }}</span>
                </div>
                <div class="flex justify-between">
                    <span>PRs:</span>
                    <span class="font-bold">{{ $data['this_week']['pull_requests'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Issues:</span>
                    <span class="font-bold">{{ $data['this_week']['issues_closed'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Streak:</span>
                    <span class="font-bold">🔥 {{ $data['this_week']['streak_days'] ?? 0 }}d</span>
                </div>
            </div>
        </div>
        
        <!-- Active Repositories (ALL repos with commits today) -->
        @if(isset($data['repositories']) && count($data['repositories']) > 0)
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-2 mb-2">
                <div class="text-xs text-white text-opacity-80 font-semibold mb-1">
                    📂 Active Repos ({{ count($data['repositories']) }})
                </div>
                <div class="space-y-1 max-h-24 overflow-y-auto">
                    @foreach($data['repositories'] as $index => $repo)
                        <div class="flex items-center justify-between text-xs contribution-square bg-white bg-opacity-10 rounded p-1"
                             style="animation-delay: {{ $index * 0.1 }}s;">
                            <div class="flex items-center gap-1 flex-1 min-w-0">
                                <span class="text-white drop-shadow truncate text-xs">{{ $repo['name'] }}</span>
                            </div>
                            <div class="flex items-center gap-2 ml-2">
                                <span class="text-green-300 font-bold">{{ $repo['commits'] ?? 0 }} commits</span>
                                @if(isset($repo['stars']) && $repo['stars'] > 0)
                                    <span class="text-yellow-200 text-xs">⭐ {{ $repo['stars'] }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-lg p-2 mb-2 text-center">
                <div class="text-xs text-white text-opacity-60">No active repositories today</div>
            </div>
        @endif
        
        <!-- Pull Requests & Issues -->
        <div class="grid grid-cols-2 gap-1 mb-2">
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-2">
                <div class="text-xs text-white text-opacity-80 mb-1">Pull Requests</div>
                <div class="flex items-center justify-between text-xs text-white">
                    <span>Open:</span>
                    <span class="font-bold">{{ $data['pull_requests']['open'] ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between text-xs text-white">
                    <span>Merged today:</span>
                    <span class="font-bold text-green-200">{{ $data['pull_requests']['merged_today'] ?? 0 }}</span>
                </div>
            </div>
            
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-2">
                <div class="text-xs text-white text-opacity-80 mb-1">Issues</div>
                <div class="flex items-center justify-between text-xs text-white">
                    <span>Open:</span>
                    <span class="font-bold">{{ $data['issues']['open'] ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between text-xs text-white">
                    <span>Closed today:</span>
                    <span class="font-bold text-green-200">{{ $data['issues']['closed_today'] ?? 0 }}</span>
                </div>
            </div>
        </div>
        
        <!-- Recent Commits -->
        @if(isset($data['recent_commits']) && count($data['recent_commits']) > 0)
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-2">
                <div class="text-xs text-white text-opacity-80 font-semibold mb-1">💬 Recent Commits</div>
                <div class="space-y-1 max-h-32 overflow-y-auto">
                    @foreach($data['recent_commits'] as $index => $commit)
                        <div class="text-xs bg-white bg-opacity-10 rounded p-1 commit-item"
                             style="animation-delay: {{ $index * 0.1 }}s;">
                            <div class="text-white drop-shadow truncate">{{ $commit['message'] }}</div>
                            <div class="flex items-center justify-between text-white text-opacity-70 mt-0.5">
                                <span>{{ \Carbon\Carbon::parse($commit['time'])->diffForHumans() }}</span>
                                <span class="font-mono">{{ $commit['sha'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        
        <!-- Footer with Status Light and Timestamp -->
        <div class="flex items-center justify-between text-xs text-white text-opacity-70 pt-2 mt-2 border-t border-white border-opacity-20">
            <!-- Status Light -->
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full transition-colors duration-300"
                      :class="{
                          'bg-gray-400': statusLight === 'gray',
                          'bg-yellow-400 animate-pulse': statusLight === 'yellow',
                          'bg-green-400': statusLight === 'green',
                          'bg-red-400': statusLight === 'red'
                      }"
                      :title="statusLight === 'yellow' ? 'Oppdaterer...' : statusLight === 'green' ? 'Oppdatert' : statusLight === 'red' ? 'Feil' : 'Inaktiv'"></span>
                <span x-show="statusIcon" class="font-bold" x-text="statusIcon"></span>
                <span x-show="statusLight === 'yellow'">Oppdaterer...</span>
                <span x-show="statusLight === 'green'">Oppdatert</span>
                <span x-show="statusLight === 'red'">Feil</span>
            </div>
            
            <!-- Timestamp -->
            <div class="flex items-center gap-2">
                <span class="font-mono">@{{ $username }}</span>
                <span x-text="lastUpdate || 'Starter...'"></span>
            </div>
        </div>
    </div>
</div>
