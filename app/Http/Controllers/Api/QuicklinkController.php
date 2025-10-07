<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class QuicklinkController extends Controller
{
    /**
     * Get quicklinks for current user
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $links = Cache::get("quicklinks.user.{$userId}", []);
        
        return response()->json([
            'success' => true,
            'links' => $links,
        ]);
    }
    
    /**
     * Add new quicklink
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:100',
            'url' => 'required|url|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->user()->id;
        $links = Cache::get("quicklinks.user.{$userId}", []);
        
        $newLink = [
            'id' => uniqid(),
            'title' => $request->input('title'),
            'url' => $request->input('url'),
            'created_at' => now()->toIso8601String(),
        ];
        
        $links[] = $newLink;
        
        Cache::put("quicklinks.user.{$userId}", $links, 60 * 60 * 24 * 365); // 1 year
        
        return response()->json([
            'success' => true,
            'link' => $newLink,
            'links' => $links,
        ]);
    }
    
    /**
     * Delete quicklinks
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->user()->id;
        $links = Cache::get("quicklinks.user.{$userId}", []);
        $idsToDelete = $request->input('ids');
        
        // Filter out links with matching IDs
        $links = array_values(array_filter($links, function($link) use ($idsToDelete) {
            return !in_array($link['id'], $idsToDelete);
        }));
        
        Cache::put("quicklinks.user.{$userId}", $links, 60 * 60 * 24 * 365);
        
        return response()->json([
            'success' => true,
            'deleted_count' => count($idsToDelete),
            'links' => $links,
        ]);
    }
}
