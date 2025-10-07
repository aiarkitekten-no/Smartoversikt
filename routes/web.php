<?php

use App\Http\Controllers\Api\WidgetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserWidgetController;
use App\Http\Controllers\WidgetAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Test routes (remove in production)
if (app()->environment('local')) {
    require __DIR__.'/test-github.php';
}

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/mail-accounts', [SettingsController::class, 'storeMailAccount'])->name('settings.mail-accounts.store');
    Route::patch('/settings/mail-accounts/{mailAccount}', [SettingsController::class, 'updateMailAccount'])->name('settings.mail-accounts.update');
    Route::delete('/settings/mail-accounts/{mailAccount}', [SettingsController::class, 'destroyMailAccount'])->name('settings.mail-accounts.destroy');
    Route::patch('/settings/weather', [SettingsController::class, 'updateWeather'])->name('settings.weather.update');
    Route::post('/settings/rss-feeds', [SettingsController::class, 'storeRssFeed'])->name('settings.rss-feeds.store');
    Route::patch('/settings/rss-feeds/{rssFeed}', [SettingsController::class, 'updateRssFeed'])->name('settings.rss-feeds.update');
    Route::delete('/settings/rss-feeds/{rssFeed}', [SettingsController::class, 'destroyRssFeed'])->name('settings.rss-feeds.destroy');

    // Widget API (moved from api.php to use session auth)
    Route::prefix('api/widgets')->name('api.widgets.')->group(function () {
        Route::get('/', [WidgetController::class, 'index'])->name('index');
        Route::get('/{key}', [WidgetController::class, 'show'])->name('show');
        Route::post('/{key}/refresh', [WidgetController::class, 'refresh'])->name('refresh');
    });

    // User Widget Management
    Route::prefix('my-widgets')->name('user-widgets.')->group(function () {
        Route::get('/available', [UserWidgetController::class, 'available'])->name('available');
        Route::post('/', [UserWidgetController::class, 'store'])->name('store');
        Route::patch('/{userWidget}', [UserWidgetController::class, 'update'])->name('update');
        Route::post('/{userWidget}/toggle', [UserWidgetController::class, 'toggle'])->name('toggle');
        Route::post('/positions', [UserWidgetController::class, 'updatePositions'])->name('positions');
        Route::delete('/{userWidget}', [UserWidgetController::class, 'destroy'])->name('destroy');
    });

    // Widget Administration
    Route::prefix('admin/widgets')->name('admin.widgets.')->group(function () {
        Route::get('/', [WidgetAdminController::class, 'index'])->name('index');
        Route::patch('/{widget}', [WidgetAdminController::class, 'update'])->name('update');
        Route::post('/{widget}/toggle', [WidgetAdminController::class, 'toggle'])->name('toggle');
        Route::post('/order', [WidgetAdminController::class, 'updateOrder'])->name('order');
        Route::delete('/{widget}', [WidgetAdminController::class, 'destroy'])->name('destroy');
        Route::post('/bulk', [WidgetAdminController::class, 'bulkAction'])->name('bulk');
    });
});

require __DIR__.'/auth.php';
