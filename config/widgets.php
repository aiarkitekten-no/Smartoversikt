<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Widget Katalog
    |--------------------------------------------------------------------------
    |
    | Fase 2 - Tilgjengelige widgets med konfigurasjon
    |
    */

    'catalog' => [
        // Fase 3 - System widgets
        'system.uptime' => [
            'name' => 'Oppetid & HTTP',
            'description' => 'Server uptime og HTTP response tid',
            'category' => 'system',
            'refresh_interval' => 60, // 1 min
            'fetcher' => \App\Services\Widgets\SystemUptimeFetcher::class,
        ],

        'system.cpu-ram' => [
            'name' => 'CPU, RAM & Disk I/O',
            'description' => 'Prosessor, minne bruk og disk I/O',
            'category' => 'system',
            'refresh_interval' => 30, // 30 sek
            'fetcher' => \App\Services\Widgets\SystemCpuRamFetcher::class,
        ],

        'system.disk-usage' => [
            'name' => 'Diskplass & Nettverk',
            'description' => 'Diskplass for viktige partisjoner og nettverkstrafikk',
            'category' => 'system',
            'refresh_interval' => 60, // 1 min
            'fetcher' => \App\Services\Widgets\SystemDiskUsageFetcher::class,
        ],

        'system.network' => [
            'name' => 'Nettverkstrafikk',
            'description' => 'Innkommende og utgående nettverkstrafikk',
            'category' => 'system',
            'refresh_interval' => 30, // 30 sek
            'fetcher' => \App\Services\Widgets\SystemNetworkFetcher::class,
        ],

        'system.disk-io' => [
            'name' => 'Disk I/O',
            'description' => 'Disk lesing og skriving',
            'category' => 'system',
            'refresh_interval' => 30, // 30 sek
            'fetcher' => \App\Services\Widgets\SystemDiskIOFetcher::class,
        ],

        'system.cron-jobs' => [
            'name' => 'Scheduled Jobs',
            'description' => 'Laravel scheduled jobs oversikt',
            'category' => 'system',
            'refresh_interval' => 120, // 2 min
            'fetcher' => \App\Services\Widgets\SystemCronJobsFetcher::class,
        ],

        'system.error-log' => [
            'name' => 'Error Monitor',
            'description' => 'Laravel, PHP og Nginx feil',
            'category' => 'system',
            'refresh_interval' => 60, // 1 min
            'fetcher' => \App\Services\Widgets\SystemErrorLogFetcher::class,
        ],

        'system.disk' => [
            'name' => 'Disk & I/O',
            'description' => 'Diskplass og I/O statistikk',
            'category' => 'system',
            'refresh_interval' => 120, // 2 min
            'fetcher' => \App\Services\Widgets\SystemDiskFetcher::class,
        ],

        // Fase 4+ - Mail widgets
        'mail.queue' => [
            'name' => 'Mail Queue',
            'description' => 'Laravel og system mail queue status',
            'category' => 'mail',
            'refresh_interval' => 60,
            'fetcher' => \App\Services\Widgets\MailQueueFetcher::class,
        ],

        'mail.imap' => [
            'name' => 'IMAP Mailbox',
            'description' => 'E-postboks statistikk via IMAP',
            'category' => 'mail',
            'refresh_interval' => 300,
            'fetcher' => \App\Services\Widgets\MailImapFetcher::class,
        ],

        'mail.failed-jobs' => [
            'name' => 'Failed Jobs',
            'description' => 'Laravel failed jobs tracking',
            'category' => 'mail',
            'refresh_interval' => 120,
            'fetcher' => \App\Services\Widgets\MailFailedJobsFetcher::class,
        ],

        'mail.log' => [
            'name' => 'Mail Log',
            'description' => 'Mail statistikk fra server logg',
            'category' => 'mail',
            'refresh_interval' => 180,
            'fetcher' => \App\Services\Widgets\MailLogFetcher::class,
        ],

        'mail.smtp' => [
            'name' => 'SMTP Status',
            'description' => 'Postfix/SMTP server status',
            'category' => 'mail',
            'refresh_interval' => 60,
            'fetcher' => \App\Services\Widgets\MailSmtpFetcher::class,
        ],

        // Weather & Power widgets
        'weather.yr' => [
            'name' => 'Vær (Yr.no)',
            'description' => 'Værmelding fra Yr.no',
            'category' => 'weather',
            'refresh_interval' => 1800, // 30 min
            'fetcher' => \App\Services\Widgets\WeatherYrFetcher::class,
        ],

        'weather.power-price' => [
            'name' => 'Strømpriser',
            'description' => 'Strømpriser fra Nordpool',
            'category' => 'weather',
            'refresh_interval' => 3600, // 1 hour
            'fetcher' => \App\Services\Widgets\WeatherPowerPriceFetcher::class,
        ],

        // Analytics widgets
        'analytics.smartesider' => [
            'name' => 'Smartesider Stats',
            'description' => 'Besøksstatistikk for Smartesider.no',
            'category' => 'analytics',
            'refresh_interval' => 300, // 5 min
            'fetcher' => \App\Services\Widgets\AnalyticsSmartesiderFetcher::class,
        ],

        'analytics.traffic' => [
            'name' => 'Web Traffic',
            'description' => 'Sanntids webserver-trafikk',
            'category' => 'analytics',
            'refresh_interval' => 60,
            'fetcher' => \App\Services\Widgets\AnalyticsTrafficFetcher::class,
        ],

        // CRM widgets
        'crm.pipedrive' => [
            'name' => 'Pipedrive CRM',
            'description' => 'Salg og pipeline-oversikt',
            'category' => 'crm',
            'refresh_interval' => 600, // 10 min
            'fetcher' => \App\Services\Widgets\CrmPipedriveFetcher::class,
        ],

        'crm.support' => [
            'name' => 'Support Tickets',
            'description' => 'Kundesupport-oversikt',
            'category' => 'crm',
            'refresh_interval' => 300, // 5 min
            'fetcher' => \App\Services\Widgets\CrmSupportFetcher::class,
        ],

        // News widgets
        'news.rss' => [
            'name' => 'RSS Nyheter',
            'description' => 'Siste nyheter fra RSS-feeds',
            'category' => 'news',
            'refresh_interval' => 600, // 10 min
            'fetcher' => \App\Services\Widgets\NewsRssFetcher::class,
        ],

        // Monitoring widgets
        'monitoring.uptime' => [
            'name' => 'Website Uptime',
            'description' => 'Overvåk oppetid for flere nettsider',
            'category' => 'monitoring',
            'refresh_interval' => 60, // 1 min
            'fetcher' => \App\Services\Widgets\MonitoringUptimeFetcher::class,
            'settings' => [
                'websites' => [
                    'type' => 'array',
                    'label' => 'Nettsider å overvåke',
                    'default' => [
                        ['name' => 'Smartesider', 'url' => 'https://smartesider.no']
                    ],
                ],
                'check_interval' => [
                    'type' => 'select',
                    'label' => 'Sjekk-intervall',
                    'options' => [
                        '30' => 'Hver 30. sekund',
                        '60' => 'Hvert minutt',
                        '300' => 'Hver 5. minutt',
                        '600' => 'Hver 10. minutt',
                    ],
                    'default' => '60',
                ],
                'timeout' => [
                    'type' => 'number',
                    'label' => 'Timeout (sekunder)',
                    'min' => 1,
                    'max' => 30,
                    'default' => 5,
                ],
            ],
        ],
        // Business widgets
        'business.stripe' => [
            'name' => 'Stripe Dashboard',
            'description' => 'Salg og transaksjoner fra Stripe',
            'category' => 'business',
            'refresh_interval' => 300, // 5 min
            'fetcher' => \App\Services\Widgets\BusinessStripeFetcher::class,
        ],

        // Development widgets
        'dev.github' => [
            'name' => 'GitHub Activity',
            'description' => 'Commits, PRs og issues fra GitHub',
            'category' => 'development',
            'refresh_interval' => 300, // 5 min
            'fetcher' => \App\Services\Widgets\DevGithubFetcher::class,
            'settings' => [
                'username' => [
                    'type' => 'text',
                    'label' => 'GitHub Brukernavn',
                    'placeholder' => 'octocat',
                    'required' => true,
                ],
                'token' => [
                    'type' => 'password',
                    'label' => 'GitHub Personal Access Token',
                    'placeholder' => 'ghp_xxxxxxxxxxxx',
                    'required' => true,
                    'help' => 'Opprett token på: Settings → Developer settings → Personal access tokens',
                ],
                'show_private' => [
                    'type' => 'checkbox',
                    'label' => 'Vis private repositories',
                    'default' => false,
                ],
            ],
        ],

        // Demo widget
        'demo.clock' => [
            'name' => 'Live Klokke',
            'description' => 'Enkel klokke for testing av widget-systemet',
            'category' => 'demo',
            'refresh_interval' => 10, // 10 sek
            'fetcher' => \App\Services\Widgets\DemoClockFetcher::class,
        ],

        // Security widgets
        'security.ssl-certs' => [
            'name' => 'SSL-sertifikater',
            'description' => 'SSL sertifikat utløpsdatoer',
            'category' => 'security',
            'refresh_interval' => 3600, // 1 time
            'fetcher' => \App\Services\Widgets\SecuritySslCertsFetcher::class,
        ],

                'security.events' => [
            'name' => 'Sikkerhetshendelser',
            'description' => 'SSH feil, web autentisering, Fail2ban status',
            'category' => 'security',
            'refresh_interval' => 30, // 30 sek - KRITISK
            'fetcher' => \App\Services\Widgets\SecurityEventsFetcher::class,
        ],

        // Project Management
        'project.trello' => [
            'name' => 'Trello Oppgaver',
            'description' => 'Oversikt over Trello oppgaver og frister',
            'category' => 'project',
            'refresh_interval' => 300, // 5 min
            'fetcher' => \App\Services\Widgets\ProjectTrelloFetcher::class,
        ],

        // Business & Economy
        'business.folio' => [
            'name' => 'Folio Økonomi',
            'description' => 'Saldo og siste transaksjoner fra Folio',
            'category' => 'business',
            'refresh_interval' => 300, // 5 min
            'fetcher' => \App\Services\Widgets\BusinessFolioFetcher::class,
        ],

        // Communication
        'communication.sms' => [
            'name' => 'Send SMS',
            'description' => 'Send SMS direkte fra dashboardet',
            'category' => 'communication',
            'refresh_interval' => 300,
            'fetcher' => \App\Services\Widgets\CommunicationSmsFetcher::class,
        ],

        'communication.phonero' => [
            'name' => 'Phonero Telefoni',
            'description' => 'Ring direkte fra dashboardet og se samtalehistorikk',
            'category' => 'communication',
            'refresh_interval' => 60,
            'fetcher' => \App\Services\Widgets\CommunicationPhoneroFetcher::class,
        ],

        // ----------
        // Security Widgets
        // ----------

        // Fase 4 - Development widgets
    ],

    /*
    |--------------------------------------------------------------------------
    | Widget Kategorier
    |--------------------------------------------------------------------------
    */

    'categories' => [
        'system' => [
            'name' => 'System & Server',
            'icon' => '🖥️',
            'color' => 'blue',
        ],
        'mail' => [
            'name' => 'E-post',
            'icon' => '📧',
            'color' => 'green',
        ],
        'analytics' => [
            'name' => 'Analyse & Markedsføring',
            'icon' => '📊',
            'color' => 'purple',
        ],
        'weather' => [
            'name' => 'Vær & Strøm',
            'icon' => '🌤️',
            'color' => 'yellow',
        ],
        'crm' => [
            'name' => 'CRM & Support',
            'icon' => '👥',
            'color' => 'indigo',
        ],
        'news' => [
            'name' => 'Nyheter',
            'icon' => '📰',
            'color' => 'orange',
        ],
        'monitoring' => [
            'name' => 'Monitoring',
            'icon' => '🌐',
            'color' => 'teal',
        ],
        'business' => [
            'name' => 'Business & Økonomi',
            'icon' => '💰',
            'color' => 'emerald',
        ],
        'communication' => [
            'name' => 'Kommunikasjon',
            'icon' => '📱',
            'color' => 'green',
        ],
        'development' => [
            'name' => 'Development',
            'icon' => '🐙',
            'color' => 'slate',
        ],
        'demo' => [
            'name' => 'Demo & Testing',
            'icon' => '🧪',
            'color' => 'gray',
        ],
        'security' => [
            'name' => 'Sikkerhet',
            'icon' => '🔐',
            'color' => 'red',
        ],
        'project' => [
            'name' => 'Prosjekter',
            'icon' => '📋',
            'color' => 'indigo',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Standard Innstillinger
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'refresh_interval' => 300, // 5 min default
        'cache_ttl' => 600, // 10 min cache
        'timeout' => 10, // 10 sek timeout for fetchers
    ],

    /*
    |--------------------------------------------------------------------------
    | Mail Widget Settings
    |--------------------------------------------------------------------------
    */

    'mail' => [
        // IMAP Settings
        'imap_host' => env('IMAP_HOST'),
        'imap_port' => env('IMAP_PORT', 993),
        'imap_username' => env('IMAP_USERNAME'),
        'imap_password' => env('IMAP_PASSWORD'),
        'imap_encryption' => env('IMAP_ENCRYPTION', 'ssl'), // ssl or tls
        
        // SMTP Settings (for monitoring)
        'smtp_host' => env('SMTP_HOST', env('MAIL_HOST')),
        'smtp_port' => env('SMTP_PORT', env('MAIL_PORT', 587)),
        'smtp_username' => env('SMTP_USERNAME', env('MAIL_USERNAME')),
        'smtp_encryption' => env('SMTP_ENCRYPTION', 'tls'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SSL Widget Settings
    |--------------------------------------------------------------------------
    */

    'ssl_domains' => [
        // Add domains to check SSL certificates for
        'smartesider.no',
        'www.smartesider.no',
        'nytt.smartesider.no',
        'status.smartesider.no',
        'digitalkontoret.no',
        'gavetre.no',
        'herimoss.no',
        'husselskapet.no',
        'mosskonsult.no',
        // Add more domains as needed
    ],

    /*
    |--------------------------------------------------------------------------
    | Weather Widget Settings
    |--------------------------------------------------------------------------
    */

    'weather' => [
        'latitude' => env('WEATHER_LAT', 59.4344), // Moss, Østfold
        'longitude' => env('WEATHER_LON', 10.6574),
        'location' => env('WEATHER_LOCATION', 'Moss, Østfold'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Trello Widget Settings
    |--------------------------------------------------------------------------
    */

    'trello' => [
        'api_key' => env('TRELLO_API_KEY'),
        'api_token' => env('TRELLO_API_TOKEN'),
        'board_id' => env('TRELLO_BOARD_ID'),
    ],
];
