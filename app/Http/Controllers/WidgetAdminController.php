<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkWidgetActionRequest;
use App\Http\Requests\UpdateWidgetRequest;
use App\Models\Widget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class WidgetAdminController extends Controller
{
    /**
     * Display widget administration page
     */
    public function index(Request $request)
    {
        $query = Widget::withoutGlobalScope('ordered')->with('latestSnapshot');

        // Filter by category if specified
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Filter by status if specified
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $widgets = $query->orderBy('order')->orderBy('name')->get();

        // Get all unique categories
        $categories = Widget::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('admin.widgets.index', compact('widgets', 'categories'));
    }

    /**
     * Update widget settings
     */
    public function update(UpdateWidgetRequest $request, Widget $widget)
    {
        $widget->update($request->validated());

        return back()->with('success', "Widget '{$widget->name}' ble oppdatert.");
    }

    /**
     * Toggle widget enabled/disabled status
     */
    public function toggle(Widget $widget)
    {
        $widget->update(['is_active' => !$widget->is_active]);

        $status = $widget->is_active ? 'aktivert' : 'deaktivert';
        return back()->with('success', "Widget '{$widget->name}' ble {$status}.");
    }

    /**
     * Update widget order
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|integer|exists:widgets,id',
            'orders.*.order' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->orders as $item) {
                Widget::where('id', $item['id'])->update(['order' => $item['order']]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Rekkefølge oppdatert.']);
    }

    /**
     * Delete a widget
     */
    public function destroy(Widget $widget)
    {
        $name = $widget->name;
        
        // Delete associated snapshots
        $widget->snapshots()->delete();
        
        // Delete the widget
        $widget->delete();

        return back()->with('success', "Widget '{$name}' ble slettet.");
    }

    /**
     * Perform bulk actions on multiple widgets
     */
    public function bulkAction(BulkWidgetActionRequest $request)
    {
        $widgetIds = $request->widget_ids;
        $action = $request->action;

        $count = 0;

        switch ($action) {
            case 'enable':
                $count = Widget::whereIn('id', $widgetIds)->update(['is_active' => true]);
                $message = "{$count} widget(s) ble aktivert.";
                break;

            case 'disable':
                $count = Widget::whereIn('id', $widgetIds)->update(['is_active' => false]);
                $message = "{$count} widget(s) ble deaktivert.";
                break;

            case 'refresh':
                $widgets = Widget::whereIn('id', $widgetIds)->get();
                foreach ($widgets as $widget) {
                    Artisan::call('widgets:refresh', [
                        '--widget' => $widget->key,
                        '--force' => true,
                    ]);
                    $count++;
                }
                $message = "{$count} widget(s) ble oppdatert.";
                break;

            default:
                return back()->with('error', 'Ugyldig handling.');
        }

        return back()->with('success', $message);
    }
}

