<?php

namespace App\Services\Widgets;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DevGithubFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'dev.github';
    
    /**
     * Refresh interval: 300 seconds (5 min)
     */
    protected int $refreshIntervalSeconds = 300;
    
    /**
     * Fetch GitHub activity data
     * 
     * @return array
     */
    protected function fetchData(): array
    {
        $username = $this->getGithubUsername();
        $token = $this->getGithubToken();
        
        // If we have real credentials, try to fetch from GitHub API
        if ($username && $token && $username !== 'octocat') {
            try {
                return $this->fetchRealGithubData($username, $token);
            } catch (\Exception $e) {
                Log::warning('GitHub API fetch failed, falling back to mock data: ' . $e->getMessage());
            }
        }
        
        // Fallback to mock data for demo
        $today = $this->getTodayActivity();
        $thisWeek = $this->getWeekActivity();
        
        return [
            'timestamp' => Carbon::now()->toIso8601String(),
            'username' => $username,
            'today' => $today,
            'this_week' => $thisWeek,
            'repositories' => $this->getTopRepositories(),
            'recent_commits' => $this->getRecentCommits(),
            'pull_requests' => $this->getPullRequests(),
            'issues' => $this->getIssues(),
            'activity_status' => $this->determineActivityStatus($today['commits']),
        ];
    }
    
    /**
     * Get GitHub username from settings
     */
    protected function getGithubUsername(): string
    {
        return $this->userWidget->settings['username'] ?? 'octocat';
    }
    
    /**
     * Get GitHub token from settings
     */
    protected function getGithubToken(): ?string
    {
        return $this->userWidget->settings['token'] ?? null;
    }
    
    /**
     * Fetch real data from GitHub API
     */
    protected function fetchRealGithubData(string $username, string $token): array
    {
        Log::info("Fetching GitHub data for user: {$username}");
        
        $headers = [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'Smartesider-Dashboard',
        ];
        
        // Fetch user data
        $userResponse = Http::withHeaders($headers)->get("https://api.github.com/users/{$username}");
        
        if (!$userResponse->successful()) {
            Log::error("GitHub API user fetch failed: " . $userResponse->status() . " - " . $userResponse->body());
            throw new \Exception("GitHub API failed: " . $userResponse->status());
        }
        
        $user = $userResponse->json();
        Log::info("GitHub user fetched: " . ($user['login'] ?? 'unknown'));
        
        // Fetch events (commits, PRs, etc)
        $eventsResponse = Http::withHeaders($headers)
            ->get("https://api.github.com/users/{$username}/events");
            
        if (!$eventsResponse->successful()) {
            Log::error("GitHub API events fetch failed: " . $eventsResponse->status());
            throw new \Exception("GitHub API events failed: " . $eventsResponse->status());
        }
        
        $events = $eventsResponse->json();
        Log::info("GitHub events count: " . count($events));
        
        // Process ALL events for statistics
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        
        $todayStats = [
            'commits' => 0,
            'lines_added' => 0,
            'lines_deleted' => 0,
            'prs' => 0,
            'issues' => 0,
            'repos' => [],
        ];
        
        $weekStats = [
            'commits' => 0,
            'prs' => 0,
            'issues' => 0,
            'repos' => [],
        ];
        
        foreach ($events as $event) {
            $eventDate = Carbon::parse($event['created_at']);
            $isToday = $eventDate->isToday();
            $isThisWeek = $eventDate->gte($weekStart);
            $repoName = $event['repo']['name'] ?? 'unknown';
            
            if ($event['type'] === 'PushEvent') {
                $payload = $event['payload'] ?? [];
                $commitCount = $payload['size'] ?? count($payload['commits'] ?? []);
                
                if ($isToday) {
                    $todayStats['commits'] += $commitCount;
                    $todayStats['repos'][$repoName] = ($todayStats['repos'][$repoName] ?? 0) + $commitCount;
                    
                    // Estimate lines changed (GitHub API doesn't provide this in events)
                    $todayStats['lines_added'] += $commitCount * 25; // Rough estimate
                    $todayStats['lines_deleted'] += $commitCount * 8; // Rough estimate
                }
                
                if ($isThisWeek) {
                    $weekStats['commits'] += $commitCount;
                    $weekStats['repos'][$repoName] = true;
                }
            } elseif ($event['type'] === 'PullRequestEvent') {
                if ($isToday) $todayStats['prs']++;
                if ($isThisWeek) $weekStats['prs']++;
            } elseif ($event['type'] === 'IssuesEvent') {
                if ($isToday) $todayStats['issues']++;
                if ($isThisWeek) $weekStats['issues']++;
            }
        }
        
        Log::info("Today: commits={$todayStats['commits']}, repos=" . count($todayStats['repos']));
        Log::info("Week: commits={$weekStats['commits']}, repos=" . count($weekStats['repos']));
        
        // If no commits today from Events API, try fetching directly from repos
        // (Events API has 30s-6h latency, but Commits API is more real-time)
        if ($todayStats['commits'] === 0) {
            Log::info("No commits found in Events API, fetching directly from repos...");
            $realtimeCommits = $this->fetchTodaysCommitsFromRepos($username, $token);
            
            if ($realtimeCommits['commits'] > 0) {
                Log::info("Found {$realtimeCommits['commits']} commits from Commits API");
                $todayStats['commits'] = $realtimeCommits['commits'];
                $todayStats['lines_added'] = $realtimeCommits['commits'] * 25;
                $todayStats['lines_deleted'] = $realtimeCommits['commits'] * 8;
                $todayStats['repos'] = $realtimeCommits['repos'];
            }
        }
        
        // Extract active repositories with commit counts
        $activeRepos = [];
        foreach ($todayStats['repos'] as $repoName => $commitCount) {
            $activeRepos[] = [
                'name' => $repoName,
                'commits' => $commitCount,
                'stars' => 0, // Will be filled if we fetch repo details
            ];
        }
        
        // Calculate streak
        $streakDays = $this->calculateStreak($events);
        
        return [
            'timestamp' => Carbon::now()->toIso8601String(),
            'username' => $username,
            'today' => [
                'commits' => $todayStats['commits'],
                'lines_added' => $todayStats['lines_added'],
                'lines_deleted' => $todayStats['lines_deleted'],
                'pull_requests' => $todayStats['prs'],
                'issues_closed' => $todayStats['issues'],
            ],
            'this_week' => [
                'commits' => $weekStats['commits'],
                'pull_requests' => $weekStats['prs'],
                'issues_closed' => $weekStats['issues'],
                'repositories_contributed' => count($weekStats['repos']),
                'streak_days' => $streakDays,
            ],
            'repositories' => $activeRepos, // Active repos with today's commits
            'recent_commits' => $this->extractRecentCommits($events),
            'pull_requests' => $this->extractPullRequests($events),
            'issues' => $this->extractIssues($events),
            'activity_status' => $this->determineActivityStatus($todayStats['commits']),
        ];
    }
    
    /**
     * Extract recent commits from events
     */
    protected function extractRecentCommits(array $events): array
    {
        $commits = [];
        
        foreach ($events as $event) {
            if ($event['type'] === 'PushEvent' && isset($event['payload']['commits'])) {
                foreach ($event['payload']['commits'] as $commit) {
                    $commits[] = [
                        'message' => $commit['message'] ?? 'No message',
                        'repository' => $event['repo']['name'] ?? 'unknown',
                        'time' => $event['created_at'] ?? Carbon::now()->toIso8601String(), // Raw timestamp
                        'sha' => substr($commit['sha'] ?? '', 0, 7),
                    ];
                    
                    if (count($commits) >= 5) {
                        return $commits;
                    }
                }
            }
        }
        
        return $commits;
    }
    
    /**
     * Extract pull requests from events
     */
    protected function extractPullRequests(array $events): array
    {
        $prs = [];
        
        foreach ($events as $event) {
            if ($event['type'] === 'PullRequestEvent') {
                $pr = $event['payload']['pull_request'] ?? null;
                if ($pr) {
                    $prs[] = [
                        'title' => $pr['title'] ?? 'Untitled',
                        'repository' => $event['repo']['name'] ?? 'unknown',
                        'state' => $pr['state'] ?? 'unknown',
                        'number' => $pr['number'] ?? 0,
                    ];
                    
                    if (count($prs) >= 3) {
                        return $prs;
                    }
                }
            }
        }
        
        return $prs;
    }
    
    /**
     * Get today's activity
     */
    protected function getTodayActivity(): array
    {
        $hour = (int)date('H');
        $workMultiplier = ($hour >= 9 && $hour <= 17) ? 1 : 0.3;
        
        return [
            'commits' => rand(0, 15) * $workMultiplier,
            'lines_added' => rand(50, 500) * $workMultiplier,
            'lines_deleted' => rand(10, 200) * $workMultiplier,
            'pull_requests' => rand(0, 3),
            'issues_closed' => rand(0, 5),
        ];
    }
    
    /**
     * Get this week's activity
     */
    protected function getWeekActivity(): array
    {
        return [
            'commits' => rand(20, 80),
            'pull_requests' => rand(5, 20),
            'issues_closed' => rand(10, 30),
            'repositories_contributed' => rand(3, 10),
            'streak_days' => rand(1, 7),
        ];
    }
    
    /**
     * Get top repositories
     */
    protected function getTopRepositories(): array
    {
        return [
            [
                'name' => 'smartesider/dashboard',
                'commits_today' => rand(1, 8),
                'stars' => rand(10, 100),
                'language' => 'PHP',
                'status' => 'active',
            ],
            [
                'name' => 'smartesider/widgets',
                'commits_today' => rand(0, 5),
                'stars' => rand(5, 50),
                'language' => 'JavaScript',
                'status' => 'active',
            ],
            [
                'name' => 'smartesider/api',
                'commits_today' => rand(0, 3),
                'stars' => rand(15, 80),
                'language' => 'TypeScript',
                'status' => 'maintenance',
            ],
        ];
    }
    
    /**
     * Get recent commits
     */
    protected function getRecentCommits(): array
    {
        $commits = [];
        $messages = [
            'Fix bug in user authentication',
            'Add new dashboard widget',
            'Update dependencies',
            'Improve performance',
            'Add tests for API endpoints',
            'Refactor code structure',
            'Update documentation',
            'Fix styling issues',
        ];
        
        for ($i = 0; $i < 5; $i++) {
            $minutesAgo = rand(5, 480);
            $commits[] = [
                'message' => $messages[array_rand($messages)],
                'repository' => 'smartesider/dashboard',
                'time' => Carbon::now()->subMinutes($minutesAgo)->toIso8601String(),
                'additions' => rand(10, 200),
                'deletions' => rand(5, 100),
                'sha' => substr(md5(rand()), 0, 7),
            ];
        }
        
        return $commits;
    }
    
    /**
     * Get pull requests
     */
    protected function getPullRequests(): array
    {
        return [
            'open' => rand(1, 8),
            'merged_today' => rand(0, 3),
            'pending_review' => rand(0, 5),
        ];
    }
    
    /**
     * Get issues
     */
    protected function getIssues(): array
    {
        return [
            'open' => rand(5, 30),
            'closed_today' => rand(0, 5),
            'assigned_to_you' => rand(1, 10),
        ];
    }
    
    /**
     * Extract top repositories from events
     */
    protected function extractTopRepositories(array $events, string $username, string $token): array
    {
        // Count commits per repo
        $repoCommits = [];
        foreach ($events as $event) {
            if ($event['type'] === 'PushEvent') {
                $repoName = $event['repo']['name'] ?? 'unknown';
                $repoCommits[$repoName] = ($repoCommits[$repoName] ?? 0) + count($event['payload']['commits'] ?? []);
            }
        }
        
        // Sort by commit count
        arsort($repoCommits);
        
        // Get top 3 repos with details
        $repos = [];
        $headers = [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'Smartesider-Dashboard',
        ];
        
        foreach (array_slice(array_keys($repoCommits), 0, 3) as $repoName) {
            try {
                $response = Http::withHeaders($headers)->get("https://api.github.com/repos/{$repoName}");
                if ($response->successful()) {
                    $repo = $response->json();
                    $repos[] = [
                        'name' => $repoName,
                        'commits_today' => $repoCommits[$repoName],
                        'stars' => $repo['stargazers_count'] ?? 0,
                        'language' => $repo['language'] ?? 'Unknown',
                        'status' => 'active',
                    ];
                }
            } catch (\Exception $e) {
                Log::warning("Failed to fetch repo details for {$repoName}: " . $e->getMessage());
            }
        }
        
        // Fallback to mock if no repos found
        if (empty($repos)) {
            return $this->getTopRepositories();
        }
        
        return $repos;
    }
    
    /**
     * Extract issues from events
     */
    protected function extractIssues(array $events): array
    {
        $issuesOpened = 0;
        $issuesClosed = 0;
        
        foreach ($events as $event) {
            if ($event['type'] === 'IssuesEvent') {
                $action = $event['payload']['action'] ?? '';
                if ($action === 'opened') {
                    $issuesOpened++;
                } elseif ($action === 'closed') {
                    $issuesClosed++;
                }
            }
        }
        
        return [
            'open' => $issuesOpened,
            'closed_today' => $issuesClosed,
            'assigned_to_you' => $issuesOpened + $issuesClosed,
        ];
    }
    
    /**
     * Fetch today's commits directly from repositories
     * This is used as fallback when Events API hasn't updated yet (30s-6h latency)
     */
    protected function fetchTodaysCommitsFromRepos(string $username, string $token): array
    {
        $headers = [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'Smartesider-Dashboard',
        ];
        
        try {
            // Fetch user's repositories (both public and private)
            $reposResponse = Http::withHeaders($headers)
                ->get("https://api.github.com/user/repos", [
                    'affiliation' => 'owner',
                    'sort' => 'pushed',
                    'per_page' => 100,
                ]);
            
            if (!$reposResponse->successful()) {
                Log::warning("Failed to fetch repos: " . $reposResponse->status());
                return ['commits' => 0, 'repos' => []];
            }
            
            $repos = $reposResponse->json();
            $totalCommits = 0;
            $repoCommits = [];
            $today = Carbon::today()->toIso8601String();
            
            // Check each repo for commits since today
            foreach ($repos as $repo) {
                $repoName = $repo['full_name'];
                
                // Only check repos pushed to today or recently
                $pushedAt = Carbon::parse($repo['pushed_at']);
                if (!$pushedAt->isToday()) {
                    continue; // Skip repos not pushed today
                }
                
                // Fetch commits from this repo since today
                $commitsResponse = Http::withHeaders($headers)
                    ->get("https://api.github.com/repos/{$repoName}/commits", [
                        'since' => $today,
                        'per_page' => 100,
                    ]);
                
                if ($commitsResponse->successful()) {
                    $commits = $commitsResponse->json();
                    $commitCount = count($commits);
                    
                    if ($commitCount > 0) {
                        $totalCommits += $commitCount;
                        $repoCommits[$repoName] = $commitCount;
                        Log::info("Found {$commitCount} commits in {$repoName}");
                    }
                }
            }
            
            return [
                'commits' => $totalCommits,
                'repos' => $repoCommits,
            ];
            
        } catch (\Exception $e) {
            Log::error("Error fetching commits from repos: " . $e->getMessage());
            return ['commits' => 0, 'repos' => []];
        }
    }
    
    /**
     * Calculate current streak of consecutive days with commits
     */
    protected function calculateStreak(array $events): int
    {
        $daysWithActivity = [];
        
        foreach ($events as $event) {
            if ($event['type'] === 'PushEvent') {
                $date = Carbon::parse($event['created_at'])->format('Y-m-d');
                $daysWithActivity[$date] = true;
            }
        }
        
        // Count consecutive days backwards from today
        $streak = 0;
        $checkDate = Carbon::today();
        
        for ($i = 0; $i < 30; $i++) { // Check last 30 days max
            $dateStr = $checkDate->format('Y-m-d');
            if (isset($daysWithActivity[$dateStr])) {
                $streak++;
                $checkDate->subDay();
            } else {
                break; // Streak broken
            }
        }
        
        return $streak;
    }
    
    /**
     * Determine activity status
     */
    protected function determineActivityStatus(int $commits): string
    {
        if ($commits >= 10) {
            return 'very_active'; // On fire! 🔥
        } elseif ($commits >= 5) {
            return 'active'; // Productive day
        } elseif ($commits >= 1) {
            return 'moderate'; // Getting work done
        } else {
            return 'quiet'; // Quiet day
        }
    }
}
