<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SmsController extends Controller
{
    /**
     * Send SMS via SMStools API
     */
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'to' => 'required|string|min:8|max:15',
            'message' => 'required|string|max:1600',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $to = $this->normalizePhoneNumber($request->input('to'));
        $message = $request->input('message');
        $sender = env('SMS_SENDER', 'Smartesider');

        if (!$to) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid Norwegian phone number format'
            ], 422);
        }

        try {
            $result = $this->sendSmsViaSmsTools($to, $message, $sender);
            
            Log::info('SMS sent successfully', [
                'to' => $this->maskPhoneNumber($to),
                'message_id' => $result['messageid'] ?? null,
                'credits_used' => $result['credits_used'] ?? null,
                'length' => strlen($message)
            ]);

            return response()->json([
                'success' => true,
                'message_id' => $result['messageid'] ?? null,
                'status' => $result['status'] ?? 'sent',
                'credits_used' => $result['credits_used'] ?? null,
                'to' => $to,
                'message' => 'SMS sent successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'to' => $this->maskPhoneNumber($to),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to send SMS: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send SMS via SMStools API
     */
    protected function sendSmsViaSmsTools(string $to, string $message, string $sender): array
    {
        $clientId = env('SMSTOOLS_CLIENT_ID');
        $clientSecret = env('SMSTOOLS_CLIENT_SECRET');

        if (!$clientId || !$clientSecret) {
            throw new \Exception('SMS credentials not configured');
        }

        $response = Http::withHeaders([
            'X-Client-Id' => $clientId,
            'X-Client-Secret' => $clientSecret,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->timeout(30)->post('https://api.smsgatewayapi.com/v1/message/send', [
            'to' => $to,
            'message' => $message,
            'sender' => $sender,
        ]);

        if (!$response->successful()) {
            throw new \Exception('SMStools API error: ' . $response->status() . ' - ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Normalize Norwegian phone number
     */
    protected function normalizePhoneNumber(string $number): ?string
    {
        // Remove all non-digit characters
        $clean = preg_replace('/[^0-9]/', '', $number);
        
        // Remove + if present
        if (strlen($clean) > 0 && $clean[0] === '+') {
            $clean = substr($clean, 1);
        }
        
        // Handle Norwegian numbers
        if (strlen($clean) === 8) {
            // Add country code 47
            return '47' . $clean;
        } elseif (strlen($clean) === 10 && substr($clean, 0, 2) === '47') {
            // Already has country code
            return $clean;
        }
        
        // Invalid format
        return null;
    }

    /**
     * Mask phone number for logging (GDPR)
     */
    protected function maskPhoneNumber(string $number): string
    {
        if (strlen($number) <= 4) {
            return str_repeat('*', strlen($number));
        }
        
        return substr($number, 0, 2) . str_repeat('*', strlen($number) - 4) . substr($number, -2);
    }
}
