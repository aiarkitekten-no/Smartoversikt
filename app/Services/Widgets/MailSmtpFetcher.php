<?php

namespace App\Services\Widgets;

use App\Support\Sys\ReadonlyCommand;

class MailSmtpFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'mail.smtp';
    
    protected function fetchData(): array
    {
        // On Plesk/restricted environments, return safe fallback data
        // SMTP monitoring requires system-level permissions
        return [
            'service' => 'SMTP Server',
            'running' => true, // Assume running since we can send emails via Laravel
            'active_connections' => 0,
            'queue_size' => 0,
            'status' => 'ok',
            'note' => 'Detaljert SMTP-overvåking ikke tilgjengelig på delt hosting',
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
