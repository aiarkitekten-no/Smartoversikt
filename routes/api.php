<?php

use App\Http\Controllers\Api\WidgetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Use web middleware for session-based authentication
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Widget API
    Route::prefix('widgets')->group(function () {
        Route::get('/', [WidgetController::class, 'index']);
        Route::get('/{key}', [WidgetController::class, 'show']);
        Route::post('/{key}/refresh', [WidgetController::class, 'refresh']);
    });

    // Security actions
    Route::post('/security/block-ip', [App\Http\Controllers\Api\SecurityController::class, 'blockIp']);
    
    // SMS actions
    Route::post('/sms/send', [App\Http\Controllers\Api\SmsController::class, 'send']);
    
    // Phonero actions
    Route::post('/phonero/call', [App\Http\Controllers\Api\PhoneroController::class, 'clickToDial']);
    
    // Quicklinks actions
    Route::get('/quicklinks', [App\Http\Controllers\Api\QuicklinkController::class, 'index']);
    Route::post('/quicklinks', [App\Http\Controllers\Api\QuicklinkController::class, 'store']);
    Route::delete('/quicklinks', [App\Http\Controllers\Api\QuicklinkController::class, 'destroy']);
});
