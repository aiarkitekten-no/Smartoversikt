<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $widgets = [
            // Mail widgets
            ['key' => 'mail.queue', 'name' => 'Mail Queue', 'description' => 'Laravel og system mail queue status', 'category' => 'mail', 'default_refresh_interval' => 60, 'order' => 5],
            ['key' => 'mail.failed-jobs', 'name' => 'Failed Jobs', 'description' => 'Laravel failed jobs tracking', 'category' => 'mail', 'default_refresh_interval' => 120, 'order' => 6],
            ['key' => 'mail.log', 'name' => 'Mail Log', 'description' => 'Mail statistikk fra server logg', 'category' => 'mail', 'default_refresh_interval' => 180, 'order' => 7],
            ['key' => 'mail.smtp', 'name' => 'SMTP Status', 'description' => 'Postfix/SMTP server status', 'category' => 'mail', 'default_refresh_interval' => 60, 'order' => 8],
            
            // Weather widgets
            ['key' => 'weather.yr', 'name' => 'Vær (Yr.no)', 'description' => 'Værmelding fra Yr.no', 'category' => 'weather', 'default_refresh_interval' => 1800, 'order' => 9],
            ['key' => 'weather.power-price', 'name' => 'Strømpriser', 'description' => 'Strømpriser fra Nordpool', 'category' => 'weather', 'default_refresh_interval' => 3600, 'order' => 10],
            
            // Analytics widgets
            ['key' => 'analytics.smartesider', 'name' => 'Smartesider Stats', 'description' => 'Besøksstatistikk for Smartesider.no', 'category' => 'analytics', 'default_refresh_interval' => 300, 'order' => 11],
            ['key' => 'analytics.traffic', 'name' => 'Web Traffic', 'description' => 'Sanntids webserver-trafikk', 'category' => 'analytics', 'default_refresh_interval' => 60, 'order' => 12],
            
            // CRM widgets
            ['key' => 'crm.pipedrive', 'name' => 'Pipedrive CRM', 'description' => 'Salg og pipeline-oversikt', 'category' => 'crm', 'default_refresh_interval' => 600, 'order' => 13],
            ['key' => 'crm.support', 'name' => 'Support Tickets', 'description' => 'Kundesupport-oversikt', 'category' => 'crm', 'default_refresh_interval' => 300, 'order' => 14],
        ];

        foreach ($widgets as $widget) {
            DB::table('widgets')->updateOrInsert(
                ['key' => $widget['key']],
                array_merge($widget, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $widgetKeys = [
            'mail.queue',
            'mail.failed-jobs',
            'mail.log',
            'mail.smtp',
            'weather.yr',
            'weather.power-price',
            'analytics.smartesider',
            'analytics.traffic',
            'crm.pipedrive',
            'crm.support',
        ];

        DB::table('widgets')->whereIn('key', $widgetKeys)->delete();
    }
};
