<?php
# START 3e7c5a2f8d9b / Widget API Controller
# Hash: 3e7c5a2f8d9b
# Purpose: API endpoints for widget data

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Widget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    /**
     * Hent snapshot for en widget
     */
    public function show(string $key): JsonResponse
    {
        $widget = Widget::where('key', $key)->where('is_active', true)->first();

        if (!$widget) {
            return response()->json([
                'error' => 'Widget not found',
                'key' => $key,
            ], 404);
        }

        $catalog = config('widgets.catalog');
        $fetcherClass = $catalog[$key]['fetcher'] ?? null;

        if (!$fetcherClass || !class_exists($fetcherClass)) {
            return response()->json([
                'error' => 'Widget fetcher not configured',
                'key' => $key,
                'fetcher' => $fetcherClass,
            ], 500);
        }

        $fetcher = new $fetcherClass();
        $snapshot = $fetcher->getSnapshot();

        if (!$snapshot) {
            return response()->json([
                'error' => 'No snapshot available',
                'key' => $key,
            ], 503);
        }

        return response()->json([
            'key' => $key,
            'name' => $widget->name,
            'data' => $snapshot->payload,
            'fresh_at' => $snapshot->fresh_at->toIso8601String(),
            'age_seconds' => $snapshot->ageInSeconds(),
            'status' => $snapshot->status,
            'is_fresh' => $snapshot->isFresh(),
        ]);
    }

    /**
     * Force refresh av en widget
     */
    public function refresh(string $key): JsonResponse
    {
        $widget = Widget::where('key', $key)->where('is_active', true)->first();

        if (!$widget) {
            return response()->json(['error' => 'Widget not found'], 404);
        }

        $catalog = config('widgets.catalog');
        $fetcherClass = $catalog[$key]['fetcher'] ?? null;

        if (!$fetcherClass || !class_exists($fetcherClass)) {
            return response()->json(['error' => 'Widget fetcher not configured'], 500);
        }

        $fetcher = new $fetcherClass();
        $snapshot = $fetcher->refreshSnapshot();

        return response()->json([
            'message' => 'Widget refreshed',
            'key' => $key,
            'data' => $snapshot->payload,
            'fresh_at' => $snapshot->fresh_at->toIso8601String(),
            'status' => $snapshot->status,
        ]);
    }

    /**
     * List alle tilgjengelige widgets
     */
    public function index(): JsonResponse
    {
        $widgets = Widget::where('is_active', true)->get();

        return response()->json([
            'widgets' => $widgets->map(function ($widget) {
                $snapshot = $widget->latestSnapshot;

                return [
                    'key' => $widget->key,
                    'name' => $widget->name,
                    'description' => $widget->description,
                    'category' => $widget->category,
                    'refresh_interval' => $widget->default_refresh_interval,
                    'has_data' => $snapshot !== null,
                    'last_update' => $snapshot ? $snapshot->fresh_at->toIso8601String() : null,
                ];
            }),
        ]);
    }
}
# SLUTT 3e7c5a2f8d9b

