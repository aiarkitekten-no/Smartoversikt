<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Dashboard Configuration
    |--------------------------------------------------------------------------
    |
    | Fase 0.5 - Dashboard-spesifikke innstillinger
    |
    */

    'remember_days' => env('DASHBOARD_REMEMBER_DAYS', 30),
    
    'session_lifetime_minutes' => env('DASHBOARD_SESSION_LIFETIME', 120),
    
    'admin_email' => env('ADMIN_EMAIL', 'terje@smartesider.no'),
    
    'widgets' => [
        // Fylles ut i Fase 2
    ],
    
    'security' => [
        'rate_limit_per_minute' => env('DASHBOARD_RATE_LIMIT', 60),
        'https_only' => env('DASHBOARD_HTTPS_ONLY', true),
        'csrf_enabled' => true,
    ],
    
    'logging' => [
        'days' => env('LOG_RETENTION_DAYS', 30),
        'mask_secrets' => true,
    ],
];
