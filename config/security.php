<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | AbuseIPDB API Configuration
    |--------------------------------------------------------------------------
    |
    | API key for AbuseIPDB IP reputation checking
    | Get your free API key at: https://www.abuseipdb.com/register
    |
    */
    
    'abuseipdb' => [
        'enabled' => env('ABUSEIPDB_ENABLED', false),
        'api_key' => env('ABUSEIPDB_API_KEY', ''),
        'cache_ttl' => env('ABUSEIPDB_CACHE_TTL', 3600), // 1 hour
        'check_threshold' => env('ABUSEIPDB_CHECK_THRESHOLD', 75), // Report if score > 75
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Security Notifications
    |--------------------------------------------------------------------------
    |
    | Configure when and how to send security alerts
    |
    */
    
    'notifications' => [
        // Enable/disable notifications
        'enabled' => env('SECURITY_NOTIFICATIONS_ENABLED', false),
        
        // Channels to use (email, slack, both)
        'channels' => explode(',', env('SECURITY_NOTIFICATION_CHANNELS', 'email')),
        
        // Email settings
        'email' => [
            'to' => env('SECURITY_ALERT_EMAIL', 'admin@smartesider.no'),
            'from' => env('MAIL_FROM_ADDRESS', 'security@smartesider.no'),
        ],
        
        // Slack webhook
        'slack' => [
            'webhook_url' => env('SECURITY_SLACK_WEBHOOK', ''),
            'channel' => env('SECURITY_SLACK_CHANNEL', '#security-alerts'),
            'username' => env('SECURITY_SLACK_USERNAME', 'Security Bot'),
            'icon' => env('SECURITY_SLACK_ICON', ':shield:'),
        ],
        
        // Thresholds for alerts
        'thresholds' => [
            'critical' => [
                'events_per_hour' => env('SECURITY_CRITICAL_EVENTS_HOUR', 50),
                'unique_ips' => env('SECURITY_CRITICAL_UNIQUE_IPS', 20),
                'sql_injection_attempts' => env('SECURITY_CRITICAL_SQL_ATTEMPTS', 5),
            ],
            'warning' => [
                'events_per_hour' => env('SECURITY_WARNING_EVENTS_HOUR', 25),
                'unique_ips' => env('SECURITY_WARNING_UNIQUE_IPS', 10),
            ],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Risk Score Configuration
    |--------------------------------------------------------------------------
    |
    | Weight for different risk factors (total should be 100)
    |
    */
    
    'risk_scoring' => [
        'ssh_brute_force_weight' => 30,
        'sql_injection_weight' => 40,
        'distributed_attack_weight' => 20,
        'recent_activity_weight' => 10,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | GeoIP Configuration  
    |--------------------------------------------------------------------------
    */
    
    'geoip' => [
        'enabled' => env('GEOIP_ENABLED', true),
        'database_path' => env('GEOIP_DATABASE_PATH', '/usr/share/GeoIP/GeoIP.dat'),
    ],
    
];
