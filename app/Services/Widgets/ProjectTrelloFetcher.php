<?php

namespace App\Services\Widgets;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProjectTrelloFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'project.trello';

    protected string $apiKey;
    protected string $apiToken;
    protected string $boardId;

    public function __construct()
    {
        $this->apiKey = config('widgets.trello.api_key');
        $this->apiToken = config('widgets.trello.api_token');
        $this->boardId = config('widgets.trello.board_id');
    }

    public function fetchData(): array
    {
        try {
            $cards = $this->getAllCards();
            $lists = $this->getLists();
            
            return [
                'summary' => $this->getSummary($cards),
                'by_list' => $this->getCardsByList($cards, $lists),
                'overdue' => $this->getOverdueCards($cards, $lists),
                'due_today' => $this->getDueTodayCards($cards, $lists),
                'due_this_week' => $this->getDueThisWeekCards($cards, $lists),
                'completed_today' => $this->getCompletedTodayCards($cards, $lists),
                'stats' => $this->getStats($cards),
                'timestamp' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error('Trello API error: ' . $e->getMessage());
            
            return [
                'summary' => [
                    'total' => 0,
                    'overdue' => 0,
                    'due_today' => 0,
                    'due_this_week' => 0,
                    'completed_today' => 0,
                ],
                'by_list' => [],
                'overdue' => [],
                'due_today' => [],
                'due_this_week' => [],
                'completed_today' => [],
                'stats' => [],
                'error' => 'Kunne ikke hente data fra Trello: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ];
        }
    }

    protected function getAllCards(): array
    {
        $response = Http::get("https://api.trello.com/1/boards/{$this->boardId}/cards", [
            'key' => $this->apiKey,
            'token' => $this->apiToken,
            'fields' => 'name,due,dueComplete,idList,labels,idMembers,desc,url',
            'members' => 'true',
            'member_fields' => 'fullName,username,avatarUrl',
        ]);

        if (!$response->successful()) {
            throw new \Exception('Trello API request failed: ' . $response->status());
        }

        return $response->json();
    }

    protected function getLists(): array
    {
        $response = Http::get("https://api.trello.com/1/boards/{$this->boardId}/lists", [
            'key' => $this->apiKey,
            'token' => $this->apiToken,
            'fields' => 'name,pos',
        ]);

        if (!$response->successful()) {
            return [];
        }

        $lists = $response->json();
        $listsById = [];
        
        foreach ($lists as $list) {
            $listsById[$list['id']] = $list['name'];
        }

        return $listsById;
    }

    protected function getCardsByList(array $cards, array $lists): array
    {
        $byList = [];
        
        // Initialize counts for each list
        foreach ($lists as $listId => $listName) {
            $byList[$listName] = 0;
        }
        
        // Count cards per list
        foreach ($cards as $card) {
            $listName = $lists[$card['idList']] ?? 'Unknown';
            if (!isset($byList[$listName])) {
                $byList[$listName] = 0;
            }
            $byList[$listName]++;
        }
        
        return $byList;
    }

    protected function getSummary(array $cards): array
    {
        $now = Carbon::now();
        $summary = [
            'total' => 0,
            'overdue' => 0,
            'due_today' => 0,
            'due_this_week' => 0,
            'completed_today' => 0,
            'no_due_date' => 0,
        ];

        foreach ($cards as $card) {
            $summary['total']++;

            if (empty($card['due'])) {
                $summary['no_due_date']++;
                continue;
            }

            $dueDate = Carbon::parse($card['due']);
            $isComplete = $card['dueComplete'] ?? false;

            // Skip completed cards for overdue/upcoming counts
            if ($isComplete) {
                if ($dueDate->isToday()) {
                    $summary['completed_today']++;
                }
                continue;
            }

            if ($dueDate->isPast() && !$dueDate->isToday()) {
                $summary['overdue']++;
            } elseif ($dueDate->isToday()) {
                $summary['due_today']++;
            } elseif ($dueDate->isBetween($now, $now->copy()->endOfWeek())) {
                $summary['due_this_week']++;
            }
        }

        return $summary;
    }

    protected function getOverdueCards(array $cards, array $lists): array
    {
        $overdue = [];
        $now = Carbon::now();

        foreach ($cards as $card) {
            if (empty($card['due'])) {
                continue;
            }

            $dueDate = Carbon::parse($card['due']);
            $isComplete = $card['dueComplete'] ?? false;

            if (!$isComplete && $dueDate->isPast() && !$dueDate->isToday()) {
                $daysLate = abs($now->diffInDays($dueDate));
                
                $overdue[] = [
                    'id' => $card['id'],
                    'name' => $card['name'],
                    'list' => $lists[$card['idList']] ?? 'Unknown',
                    'due_date' => $dueDate->toIso8601String(),
                    'due_formatted' => $dueDate->format('d.m.Y'),
                    'days_late' => $daysLate,
                    'url' => $card['url'] ?? null,
                    'labels' => $this->formatLabels($card['labels'] ?? []),
                    'members' => $this->formatMembers($card['members'] ?? []),
                ];
            }
        }

        // Sort by most overdue first
        usort($overdue, fn($a, $b) => $b['days_late'] <=> $a['days_late']);

        return array_slice($overdue, 0, 10); // Limit to 10 most critical
    }

    protected function getDueTodayCards(array $cards, array $lists): array
    {
        $dueToday = [];

        foreach ($cards as $card) {
            if (empty($card['due'])) {
                continue;
            }

            $dueDate = Carbon::parse($card['due']);
            $isComplete = $card['dueComplete'] ?? false;

            if (!$isComplete && $dueDate->isToday()) {
                $dueToday[] = [
                    'id' => $card['id'],
                    'name' => $card['name'],
                    'list' => $lists[$card['idList']] ?? 'Unknown',
                    'due_date' => $dueDate->toIso8601String(),
                    'due_time' => $dueDate->format('H:i'),
                    'url' => $card['url'] ?? null,
                    'labels' => $this->formatLabels($card['labels'] ?? []),
                    'members' => $this->formatMembers($card['members'] ?? []),
                ];
            }
        }

        // Sort by time
        usort($dueToday, fn($a, $b) => $a['due_date'] <=> $b['due_date']);

        return $dueToday;
    }

    protected function getDueThisWeekCards(array $cards, array $lists): array
    {
        $now = Carbon::now();
        $dueThisWeek = [];

        foreach ($cards as $card) {
            if (empty($card['due'])) {
                continue;
            }

            $dueDate = Carbon::parse($card['due']);
            $isComplete = $card['dueComplete'] ?? false;

            if (!$isComplete && $dueDate->isBetween($now->copy()->addDay(), $now->copy()->endOfWeek())) {
                $dueThisWeek[] = [
                    'id' => $card['id'],
                    'name' => $card['name'],
                    'list' => $lists[$card['idList']] ?? 'Unknown',
                    'due_date' => $dueDate->toIso8601String(),
                    'due_formatted' => $dueDate->format('d.m.Y'),
                    'due_relative' => $dueDate->diffForHumans(),
                    'url' => $card['url'] ?? null,
                    'labels' => $this->formatLabels($card['labels'] ?? []),
                    'members' => $this->formatMembers($card['members'] ?? []),
                ];
            }
        }

        // Sort by date
        usort($dueThisWeek, fn($a, $b) => $a['due_date'] <=> $b['due_date']);

        return array_slice($dueThisWeek, 0, 10);
    }

    protected function getCompletedTodayCards(array $cards, array $lists): array
    {
        $completed = [];

        foreach ($cards as $card) {
            if (empty($card['due'])) {
                continue;
            }

            $dueDate = Carbon::parse($card['due']);
            $isComplete = $card['dueComplete'] ?? false;

            if ($isComplete && $dueDate->isToday()) {
                $completed[] = [
                    'id' => $card['id'],
                    'name' => $card['name'],
                    'list' => $lists[$card['idList']] ?? 'Unknown',
                    'url' => $card['url'] ?? null,
                ];
            }
        }

        return $completed;
    }

    protected function getStats(array $cards): array
    {
        $stats = [
            'total_cards' => count($cards),
            'with_due_date' => 0,
            'completed' => 0,
            'in_progress' => 0,
        ];

        foreach ($cards as $card) {
            if (!empty($card['due'])) {
                $stats['with_due_date']++;
                
                if ($card['dueComplete'] ?? false) {
                    $stats['completed']++;
                } else {
                    $stats['in_progress']++;
                }
            }
        }

        return $stats;
    }

    protected function formatLabels(array $labels): array
    {
        return array_map(function($label) {
            return [
                'name' => $label['name'] ?? '',
                'color' => $label['color'] ?? 'gray',
            ];
        }, $labels);
    }

    protected function formatMembers(array $members): array
    {
        return array_map(function($member) {
            return [
                'name' => $member['fullName'] ?? $member['username'] ?? 'Unknown',
                'username' => $member['username'] ?? '',
                'avatar' => $member['avatarUrl'] ?? null,
            ];
        }, $members);
    }
}
