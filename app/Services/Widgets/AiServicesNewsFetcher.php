<?php

namespace App\Services\Widgets;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AiServicesNewsFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'ai.services-news';
    protected int $refreshIntervalSeconds = 1800; // 30 minutes

    protected function fetchData(): array
    {
        try {
            // Cache for 30 minutes
            return Cache::remember('widget:ai-services-news', 1800, function () {
                return [
                    'news' => $this->fetchAllNews(),
                    'updated_at' => now()->toIso8601String(),
                ];
            });
        } catch (\Exception $e) {
            Log::error('AI Services News widget error: ' . $e->getMessage());
            return [
                'news' => [],
                'error' => 'Could not fetch AI news',
                'updated_at' => now()->toIso8601String(),
            ];
        }
    }

    protected function fetchAllNews(): array
    {
        $allNews = [];

        // Fetch from each source
        $allNews = array_merge($allNews, $this->fetchOpenAINews());
        $allNews = array_merge($allNews, $this->fetchVSCodeReleases());
        $allNews = array_merge($allNews, $this->fetchGitHubCopilotNews());
        $allNews = array_merge($allNews, $this->fetchAnthropicNews());

        // Sort by date (newest first)
        usort($allNews, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        // Return top 15
        return array_slice($allNews, 0, 15);
    }

    protected function fetchOpenAINews(): array
    {
        try {
            $response = Http::timeout(10)
                ->get('https://openai.com/blog/rss.xml');

            if (!$response->successful()) {
                return [];
            }

            $xml = simplexml_load_string($response->body());
            if (!$xml) {
                return [];
            }

            $items = [];
            $count = 0;

            foreach ($xml->channel->item as $item) {
                if ($count >= 5) break;

                $items[] = [
                    'title' => (string) $item->title,
                    'url' => (string) $item->link,
                    'date' => date('Y-m-d H:i:s', strtotime((string) $item->pubDate)),
                    'source' => 'OpenAI',
                    'category' => 'openai',
                    'icon' => '🤖',
                    'description' => strip_tags((string) $item->description),
                ];

                $count++;
            }

            return $items;
        } catch (\Exception $e) {
            Log::warning('Failed to fetch OpenAI news: ' . $e->getMessage());
            return [];
        }
    }

    protected function fetchVSCodeReleases(): array
    {
        try {
            // GitHub API for VS Code releases
            $response = Http::timeout(10)
                ->withHeaders([
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'Smartoversikt-Dashboard',
                ])
                ->get('https://api.github.com/repos/microsoft/vscode/releases', [
                    'per_page' => 5,
                ]);

            if (!$response->successful()) {
                return [];
            }

            $releases = $response->json();
            $items = [];

            foreach ($releases as $release) {
                $items[] = [
                    'title' => 'VS Code ' . ($release['name'] ?? $release['tag_name']),
                    'url' => $release['html_url'],
                    'date' => date('Y-m-d H:i:s', strtotime($release['published_at'])),
                    'source' => 'VS Code',
                    'category' => 'vscode',
                    'icon' => '📝',
                    'description' => $this->truncate($release['body'] ?? '', 150),
                ];
            }

            return $items;
        } catch (\Exception $e) {
            Log::warning('Failed to fetch VS Code releases: ' . $e->getMessage());
            return [];
        }
    }

    protected function fetchGitHubCopilotNews(): array
    {
        try {
            // GitHub Copilot blog via GitHub Changelog
            $response = Http::timeout(10)
                ->withHeaders([
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'Smartoversikt-Dashboard',
                ])
                ->get('https://api.github.com/repos/github/copilot-docs/commits', [
                    'per_page' => 3,
                ]);

            if (!$response->successful()) {
                return [];
            }

            $commits = $response->json();
            $items = [];

            foreach ($commits as $commit) {
                $items[] = [
                    'title' => 'Copilot: ' . ($commit['commit']['message'] ?? 'Update'),
                    'url' => 'https://github.com/features/copilot',
                    'date' => date('Y-m-d H:i:s', strtotime($commit['commit']['author']['date'])),
                    'source' => 'GitHub Copilot',
                    'category' => 'copilot',
                    'icon' => '🚀',
                    'description' => $this->truncate($commit['commit']['message'] ?? '', 150),
                ];
            }

            return $items;
        } catch (\Exception $e) {
            Log::warning('Failed to fetch GitHub Copilot news: ' . $e->getMessage());
            return [];
        }
    }

    protected function fetchAnthropicNews(): array
    {
        try {
            // Anthropic doesn't have a public RSS/API, so we'll add manual entries
            // or scrape their news page (requires more complex implementation)
            
            // For now, return empty or add manual significant announcements
            return [
                [
                    'title' => 'Claude 3.5 Sonnet - Latest Model',
                    'url' => 'https://www.anthropic.com/news',
                    'date' => '2024-10-01 00:00:00', // Update manually
                    'source' => 'Anthropic',
                    'category' => 'claude',
                    'icon' => '🧠',
                    'description' => 'Visit Anthropic news page for latest Claude updates',
                ],
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to fetch Anthropic news: ' . $e->getMessage());
            return [];
        }
    }

    protected function truncate(string $text, int $length = 150): string
    {
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length) . '...';
    }
}
