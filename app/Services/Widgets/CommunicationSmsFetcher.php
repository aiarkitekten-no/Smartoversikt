<?php

namespace App\Services\Widgets;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CommunicationSmsFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'communication.sms';
    
    /**
     * Refresh interval: 3600 seconds (1 hour) - This is an interactive widget
     */
    protected int $refreshIntervalSeconds = 3600;
    
    /**
     * Fetch SMS widget data (mostly static info for the form)
     */
    protected function fetchData(): array
    {
        return [
            'timestamp' => Carbon::now()->toIso8601String(),
            'sender' => env('SMS_SENDER', 'Smartesider'),
            'ready' => true,
            'balance' => null, // Balance not available via API
            'recent_sends' => $this->getRecentSends(),
        ];
    }
    
    /**
     * Get SMS balance from SMStools API
     * Note: Balance endpoint not available in SMStools API
     * We get credits_used in send response instead
     */
    protected function getBalance(): ?float
    {
        // SMStools API doesn't have a balance endpoint
        // Balance is only shown after sending SMS in response
        return null;
    }
    
    /**
     * Get recent SMS sends from session or cache
     */
    protected function getRecentSends(): array
    {
        // This would ideally come from a database table tracking SMS sends
        // For now, return empty array - will be populated via API calls
        return [];
    }
}
