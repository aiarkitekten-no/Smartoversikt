<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BillsController extends Controller
{
    /**
     * Get all bills for authenticated user
     */
    public function index()
    {
        $bills = Bill::where('user_id', Auth::id())
            ->orderBy('sort_order')
            ->orderBy('due_day')
            ->get();
        
        return response()->json([
            'success' => true,
            'bills' => $bills,
        ]);
    }
    
    /**
     * Create a new bill
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'due_day' => 'required|integer|min:1|max:31',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $bill = Bill::create([
                'user_id' => Auth::id(),
                'name' => $request->input('name'),
                'amount' => $request->input('amount'),
                'due_day' => $request->input('due_day'),
                'is_paid_this_month' => false,
                'sort_order' => Bill::where('user_id', Auth::id())->max('sort_order') + 1,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Forfall opprettet',
                'bill' => $bill,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Kunne ikke opprette forfall: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Toggle paid status
     */
    public function togglePaid(Request $request, $id)
    {
        $bill = Bill::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        $bill->is_paid_this_month = !$bill->is_paid_this_month;
        $bill->save();
        
        return response()->json([
            'success' => true,
            'message' => $bill->is_paid_this_month ? 'Markert som betalt' : 'Markert som ubetalt',
            'is_paid' => $bill->is_paid_this_month,
        ]);
    }
    
    /**
     * Update a bill
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
            'due_day' => 'sometimes|integer|min:1|max:31',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $bill = Bill::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        $bill->update($request->only(['name', 'amount', 'due_day']));
        
        return response()->json([
            'success' => true,
            'message' => 'Forfall oppdatert',
            'bill' => $bill,
        ]);
    }
    
    /**
     * Delete a bill
     */
    public function destroy($id)
    {
        $bill = Bill::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        $name = $bill->name;
        $bill->delete();
        
        return response()->json([
            'success' => true,
            'message' => "'{$name}' slettet",
        ]);
    }
}
