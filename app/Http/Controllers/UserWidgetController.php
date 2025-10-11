<?php

namespace App\Http\Controllers;

use App\Models\UserWidget;
use App\Models\Widget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserWidgetController extends Controller
{
    use AuthorizesRequests;
    /**
     * Add a widget to user's dashboard
     */
    public function store(Request $request)
    {
        $request->validate([
            'widget_id' => 'required|integer|exists:widgets,id',
        ]);

        $widget = Widget::findOrFail($request->widget_id);

        // Allow multiple instances of certain widgets (like RSS)
        $allowMultiple = in_array($widget->key, ['news.rss']);

        try {
            // Schema-aware: support both new (widget_id) and legacy (widget_key) columns
            $useIdColumn = Schema::hasTable('user_widgets') && Schema::hasColumn('user_widgets', 'widget_id');
            $useKeyColumn = Schema::hasTable('user_widgets') && Schema::hasColumn('user_widgets', 'widget_key');

            // Check if user already has this widget (when multiples not allowed)
            if (!$allowMultiple) {
                if ($useIdColumn) {
                    $existing = UserWidget::where('user_id', $request->user()->id)
                        ->where('widget_id', $widget->id)
                        ->first();
                } elseif ($useKeyColumn) {
                    $existing = DB::table('user_widgets')
                        ->where('user_id', $request->user()->id)
                        ->where('widget_key', $widget->key)
                        ->first();
                } else {
                    $existing = null; // unknown schema, proceed
                }

                if ($existing) {
                    return back()->with('error', 'Du har allerede denne widgeten på dashboardet.');
                }
            }

            // Compute next position
            if ($useIdColumn) {
                $maxPosition = UserWidget::where('user_id', $request->user()->id)->max('position') ?? -1;
            } elseif ($useKeyColumn) {
                $maxPosition = (DB::table('user_widgets')->where('user_id', $request->user()->id)->max('position')) ?? -1;
            } else {
                $maxPosition = -1;
            }

            // Insert
            if ($useIdColumn) {
                UserWidget::create([
                    'user_id' => $request->user()->id,
                    'widget_id' => $widget->id,
                    'position' => $maxPosition + 1,
                    'is_visible' => true,
                ]);
            } elseif ($useKeyColumn) {
                // Legacy schema insert via query builder
                DB::table('user_widgets')->insert([
                    'user_id' => $request->user()->id,
                    'widget_key' => $widget->key,
                    'settings' => json_encode(null),
                    'position' => $maxPosition + 1,
                    'size' => 1,
                    'is_visible' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // If schema is unknown, fail softly
                return back()->withErrors(['error' => 'Ukjent database-skjema for user_widgets.']);
            }

            // Return JSON for AJAX or redirect back
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Widget '{$widget->name}' ble lagt til dashboardet.",
                ]);
            }

            return back()->with('success', "Widget '{$widget->name}' ble lagt til dashboardet.");
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle duplicate key error gracefully on legacy schema when multiples not allowed
            $sqlState = $e->getCode(); // For MySQL duplicate key often 23000
            if (in_array($sqlState, ['23000', '23505'])) {
                $message = $allowMultiple
                    ? 'Kan ikke legge til flere instanser av denne widgeten grunnet databasebegrensning.'
                    : 'Du har allerede denne widgeten på dashboardet.';

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 409);
                }

                return back()->with('error', $message);
            }

            \Log::error('Error adding widget', ['error' => $e->getMessage()]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kunne ikke legge til widget: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withErrors(['error' => 'Kunne ikke legge til widget: ' . $e->getMessage()]);
        }
    }

    /**
     * Update user widget settings
     */
    public function update(Request $request, UserWidget $userWidget)
    {
        try {
            $this->authorize('update', $userWidget);

            $validated = $request->validate([
                'settings' => 'sometimes|array',
                'refresh_interval' => 'sometimes|nullable|integer|min:10|max:86400',
                'is_visible' => 'sometimes|boolean',
            ]);

            // Only update fields that were validated
            $updateData = [];
            if (isset($validated['settings'])) {
                $updateData['settings'] = $validated['settings'];
            }
            if (array_key_exists('refresh_interval', $validated)) {
                $updateData['refresh_interval'] = $validated['refresh_interval'];
            }
            if (isset($validated['is_visible'])) {
                $updateData['is_visible'] = $validated['is_visible'];
            }

            $userWidget->update($updateData);

            // Return JSON for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Widget-innstillinger oppdatert.'
                ]);
            }

            return back()->with('success', 'Widget-innstillinger oppdatert.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error in UserWidget update', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Valideringsfeil: ' . implode(', ', array_map(fn($msgs) => implode(', ', $msgs), $e->errors()))
                ], 422);
            }
            
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error updating UserWidget', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kunne ikke lagre: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Kunne ikke lagre: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle widget visibility
     */
    public function toggle(UserWidget $userWidget)
    {
        $this->authorize('update', $userWidget);

        $userWidget->update(['is_visible' => !$userWidget->is_visible]);

        $status = $userWidget->is_visible ? 'vist' : 'skjult';
        return back()->with('success', "Widget ble {$status}.");
    }

    /**
     * Update widget positions
     */
    public function updatePositions(Request $request)
    {
        $request->validate([
            'positions' => 'required|array',
            'positions.*.id' => 'required|integer|exists:user_widgets,id',
            'positions.*.position' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->positions as $item) {
                UserWidget::where('id', $item['id'])
                    ->where('user_id', $request->user()->id)
                    ->update(['position' => $item['position']]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Widget-rekkefølge oppdatert.']);
    }

    /**
     * Remove widget from user's dashboard
     */
    public function destroy(UserWidget $userWidget)
    {
        try {
            // Check if user owns this widget
            if ($userWidget->user_id !== auth()->id()) {
                abort(403, 'Unauthorized');
            }

            $widgetName = $userWidget->widget->name ?? 'Widget';
            $userWidget->delete();

            return response()->json([
                'success' => true,
                'message' => "'{$widgetName}' ble fjernet fra dashboardet."
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to delete widget: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Kunne ikke fjerne widget: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available widgets (not yet added to user's dashboard)
     */
    public function available(Request $request)
    {
        try {
            // Widgets that can be added multiple times
            $allowMultipleKeys = ['news.rss'];

            // Default empty collections for both id/key forms
            $existingIds = collect();
            $existingKeys = collect();

            // If the table exists, try to pluck either widget_id (new schema) or widget_key (old schema)
            if (Schema::hasTable('user_widgets')) {
                if (Schema::hasColumn('user_widgets', 'widget_id')) {
                    $existingIds = UserWidget::where('user_id', $request->user()->id)
                        ->pluck('widget_id');
                } elseif (Schema::hasColumn('user_widgets', 'widget_key')) {
                    // Use query builder to avoid Eloquent fillable/relations issues on legacy column
                    $existingKeys = DB::table('user_widgets')
                        ->where('user_id', $request->user()->id)
                        ->pluck('widget_key');
                }
            }

            // Get all active widgets
            $allWidgets = Widget::active()->get();

            // Filter: show widgets that are either not added yet, or allow multiple instances
            $availableWidgets = $allWidgets->filter(function ($widget) use ($existingIds, $existingKeys, $allowMultipleKeys) {
                // Always show if multiple instances are allowed
                if (in_array($widget->key, $allowMultipleKeys)) {
                    return true;
                }

                // Prefer id-based check when available
                if ($existingIds->isNotEmpty()) {
                    return !$existingIds->contains($widget->id);
                }

                // Fallback to key-based check for legacy schema
                if ($existingKeys->isNotEmpty()) {
                    return !$existingKeys->contains($widget->key);
                }

                // If we couldn't determine existing widgets, show all to avoid blocking the UI
                return true;
            })->values();

            return response()->json($availableWidgets);
        } catch (\Throwable $e) {
            \Log::error('Error in available widgets endpoint', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Fail soft with empty list to keep the modal responsive
            return response()->json([], 200);
        }
    }
}

