<?php

namespace App\Services\Widgets;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class InspirationQuoteFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'inspiration.quote';
    protected int $refreshIntervalSeconds = 86400; // 24 hours

    protected function fetchData(): array
    {
        try {
            // Cache quote for 24 hours (one quote per day)
            $cacheKey = 'widget:quote-of-day:' . now()->format('Y-m-d');
            
            return Cache::remember($cacheKey, now()->addHours(24), function () {
                return $this->fetchQuote();
            });
        } catch (\Exception $e) {
            Log::error('Quote widget error: ' . $e->getMessage());
            return $this->getFallbackQuote();
        }
    }

    protected function fetchQuote(): array
    {
        // Try ZenQuotes API first (free, no key required)
        try {
            $response = Http::timeout(5)
                ->get('https://zenquotes.io/api/today');

            if ($response->successful() && !empty($response->json())) {
                $data = $response->json()[0] ?? null;
                
                if ($data) {
                    return [
                        'quote' => $data['q'] ?? '',
                        'author' => $data['a'] ?? 'Unknown',
                        'source' => 'ZenQuotes',
                        'success' => true,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('ZenQuotes API failed: ' . $e->getMessage());
        }

        // Fallback to Quotable API
        try {
            $response = Http::timeout(5)
                ->get('https://api.quotable.io/random', [
                    'tags' => 'inspirational|motivational|wisdom',
                    'maxLength' => 200,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'quote' => $data['content'] ?? '',
                    'author' => $data['author'] ?? 'Unknown',
                    'source' => 'Quotable',
                    'success' => true,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Quotable API failed: ' . $e->getMessage());
        }

        // If both APIs fail, return fallback
        return $this->getFallbackQuote();
    }

    protected function getFallbackQuote(): array
    {
        $fallbacks = [
            [
                'quote' => 'The only way to do great work is to love what you do.',
                'author' => 'Steve Jobs',
            ],
            [
                'quote' => 'Innovation distinguishes between a leader and a follower.',
                'author' => 'Steve Jobs',
            ],
            [
                'quote' => 'The future belongs to those who believe in the beauty of their dreams.',
                'author' => 'Eleanor Roosevelt',
            ],
            [
                'quote' => 'It is never too late to be what you might have been.',
                'author' => 'George Eliot',
            ],
            [
                'quote' => 'Success is not final, failure is not fatal: it is the courage to continue that counts.',
                'author' => 'Winston Churchill',
            ],
            [
                'quote' => 'Believe you can and you\'re halfway there.',
                'author' => 'Theodore Roosevelt',
            ],
            [
                'quote' => 'The best time to plant a tree was 20 years ago. The second best time is now.',
                'author' => 'Chinese Proverb',
            ],
            [
                'quote' => 'Your time is limited, don\'t waste it living someone else\'s life.',
                'author' => 'Steve Jobs',
            ],
        ];

        // Pick quote based on day of year (consistent daily)
        $dayOfYear = now()->dayOfYear;
        $quote = $fallbacks[$dayOfYear % count($fallbacks)];

        return [
            'quote' => $quote['quote'],
            'author' => $quote['author'],
            'source' => 'Fallback',
            'success' => false,
        ];
    }
}
