<?php

namespace App\Services\Widgets;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\RssFeed;

class NewsRssFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'news.rss';
    
    protected function fetchData(): array
    {
        try {
            // Get widget settings
            $maxItems = $this->userWidget->settings['max_items'] ?? 10;
            $displayMode = $this->userWidget->settings['display_mode'] ?? 'mixed';
            $showImages = $this->userWidget->settings['show_images'] ?? false;
            $showDescriptions = $this->userWidget->settings['show_descriptions'] ?? true;
            $showSource = $this->userWidget->settings['show_source'] ?? true;
            $customUrl = $this->userWidget->settings['custom_url'] ?? null;
            
            // Get RSS feeds
            $feeds = $this->getFeeds($customUrl);
            
            $allItems = [];
            $feedResults = [];
            
            foreach ($feeds as $feedConfig) {
                $items = $this->fetchFeed($feedConfig['url'], $feedConfig['name'], $showImages);
                $feedResults[$feedConfig['name']] = $items;
                $allItems = array_merge($allItems, $items);
            }
            
            // Process items based on display mode
            $processedItems = $this->processItems($allItems, $feedResults, $displayMode, $maxItems);
            
            return [
                'items' => $processedItems,
                'total_feeds' => count($feeds),
                'feeds' => array_map(fn($f) => $f['name'], $feeds),
                'display_mode' => $displayMode,
                'show_images' => $showImages,
                'show_descriptions' => $showDescriptions,
                'show_source' => $showSource,
                'status' => 'ok',
                'timestamp' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'items' => [],
                'status' => 'unavailable',
                'timestamp' => now()->toIso8601String(),
            ];
        }
    }
    
    private function processItems(array $allItems, array $feedResults, string $displayMode, int $maxItems): array
    {
        switch ($displayMode) {
            case 'grouped':
                // Group by source, limit per source
                $grouped = [];
                foreach ($feedResults as $source => $items) {
                    usort($items, function($a, $b) {
                        return strtotime($b['date']) - strtotime($a['date']);
                    });
                    $grouped = array_merge($grouped, array_slice($items, 0, 5));
                }
                return array_slice($grouped, 0, $maxItems);
                
            case 'latest_per_source':
                // Get latest one from each source
                $latest = [];
                foreach ($feedResults as $source => $items) {
                    if (!empty($items)) {
                        usort($items, function($a, $b) {
                            return strtotime($b['date']) - strtotime($a['date']);
                        });
                        $latest[] = $items[0];
                    }
                }
                // Sort combined latest by date
                usort($latest, function($a, $b) {
                    return strtotime($b['date']) - strtotime($a['date']);
                });
                return array_slice($latest, 0, $maxItems);
                
            case 'mixed':
            default:
                // Sort all items by date, newest first
                usort($allItems, function($a, $b) {
                    return strtotime($b['date']) - strtotime($a['date']);
                });
                return array_slice($allItems, 0, $maxItems);
        }
    }
    
    private function getFeeds(?string $customUrl = null): array
    {
        $feeds = [];
        
        // Add custom URL if provided
        if ($customUrl) {
            $feeds[] = [
                'name' => 'Egendefinert',
                'url' => $customUrl,
            ];
        }
        
        // Check if widget has specific feed IDs configured
        $feedIds = $this->userWidget->settings['feed_ids'] ?? [];
        
        if (!empty($feedIds)) {
            // Get selected feeds
            $selectedFeeds = RssFeed::active()
                ->whereIn('id', $feedIds)
                ->get()
                ->map(fn($feed) => [
                    'name' => $feed->name,
                    'url' => $feed->url,
                ])
                ->toArray();
                
            $feeds = array_merge($feeds, $selectedFeeds);
        }
        
        // If no feeds selected and no custom URL, use all active feeds
        if (empty($feeds)) {
            return RssFeed::active()
                ->get()
                ->map(fn($feed) => [
                    'name' => $feed->name,
                    'url' => $feed->url,
                ])
                ->toArray();
        }
        
        return $feeds;
    }
    
    private function fetchFeed(string $url, string $sourceName, bool $showImages = false): array
    {
        try {
            // Cache each feed for 10 minutes
            $cacheKey = 'rss_feed_' . md5($url) . '_' . ($showImages ? 'img' : 'noimg');
            
            return Cache::remember($cacheKey, 600, function () use ($url, $sourceName, $showImages) {
                $response = Http::timeout(10)->get($url);
                
                if (!$response->successful()) {
                    return [];
                }
                
                $xml = simplexml_load_string($response->body());
                
                if ($xml === false) {
                    return [];
                }
                
                $items = [];
                
                // Handle both RSS 2.0 and Atom formats
                if (isset($xml->channel->item)) {
                    // RSS 2.0
                    foreach ($xml->channel->item as $item) {
                        $imageUrl = null;
                        
                        if ($showImages) {
                            // Try to extract image from media:content, enclosure, or description
                            if (isset($item->children('media', true)->content)) {
                                $imageUrl = (string) $item->children('media', true)->content->attributes()->url;
                            } elseif (isset($item->enclosure)) {
                                $type = (string) $item->enclosure->attributes()->type;
                                if (str_starts_with($type, 'image/')) {
                                    $imageUrl = (string) $item->enclosure->attributes()->url;
                                }
                            } elseif (isset($item->children('media', true)->thumbnail)) {
                                $imageUrl = (string) $item->children('media', true)->thumbnail->attributes()->url;
                            }
                        }
                        
                        $items[] = [
                            'title' => (string) $item->title,
                            'link' => (string) $item->link,
                            'description' => $this->cleanDescription((string) ($item->description ?? '')),
                            'date' => $this->parseDate((string) ($item->pubDate ?? $item->published ?? '')),
                            'source' => $sourceName,
                            'image' => $imageUrl,
                        ];
                    }
                } elseif (isset($xml->entry)) {
                    // Atom
                    foreach ($xml->entry as $entry) {
                        $imageUrl = null;
                        
                        if ($showImages && isset($entry->link)) {
                            foreach ($entry->link as $link) {
                                $rel = (string) $link->attributes()->rel;
                                $type = (string) $link->attributes()->type;
                                if ($rel === 'enclosure' && str_starts_with($type, 'image/')) {
                                    $imageUrl = (string) $link->attributes()->href;
                                    break;
                                }
                            }
                        }
                        
                        $items[] = [
                            'title' => (string) $entry->title,
                            'link' => (string) ($entry->link['href'] ?? $entry->link),
                            'description' => $this->cleanDescription((string) ($entry->summary ?? $entry->content ?? '')),
                            'date' => $this->parseDate((string) ($entry->published ?? $entry->updated ?? '')),
                            'source' => $sourceName,
                            'image' => $imageUrl,
                        ];
                    }
                }
                
                return $items;
            });
        } catch (\Exception $e) {
            return [];
        }
    }
    
    private function cleanDescription(string $description): string
    {
        // Strip HTML tags
        $clean = strip_tags($description);
        
        // Truncate to 150 characters
        if (strlen($clean) > 150) {
            $clean = substr($clean, 0, 147) . '...';
        }
        
        return trim($clean);
    }
    
    private function parseDate(string $date): string
    {
        try {
            return \Carbon\Carbon::parse($date)->toIso8601String();
        } catch (\Exception $e) {
            return now()->toIso8601String();
        }
    }
}
