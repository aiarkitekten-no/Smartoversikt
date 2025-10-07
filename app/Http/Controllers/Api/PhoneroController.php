<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PhoneroController extends Controller
{
    /**
     * Click-to-dial - Initiate call
     */
    public function clickToDial(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'destination_number' => 'required|string|min:8|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $destinationNumber = $request->input('destination_number');
        $callerNumber = config('phonero.default_agent', '41347577');
        $callerId = config('phonero.default_cli', '69020070');

        try {
            $result = $this->initiateCall($destinationNumber, $callerNumber, $callerId);
            
            Log::info('Phonero click-to-dial initiated', [
                'destination' => $destinationNumber,
                'caller' => $callerNumber,
            ]);

            return response()->json([
                'success' => true,
                'status' => 'calling',
                'message' => 'Ringer...',
                'destination' => $destinationNumber,
            ]);

        } catch (\Exception $e) {
            Log::error('Phonero click-to-dial failed', [
                'destination' => $destinationNumber,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'status' => 'failed',
                'error' => 'Kunne ikke ringe: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Initiate call via Phonero API
     */
    protected function initiateCall(string $destinationNumber, string $callerNumber, string $callerId): array
    {
        $apiUrl = config('phonero.api_url');
        $username = config('phonero.username');
        $password = config('phonero.password');
        $customerId = config('phonero.customer_id', 1);

        if (!$username || !$password) {
            throw new \Exception('Phonero credentials not configured');
        }

        // Create HTTP client with cookie jar for session persistence
        $jar = new \GuzzleHttp\Cookie\CookieJar();
        $client = Http::withOptions(['cookies' => $jar]);

        // Authenticate
        $authResponse = $client->timeout(10)->post("{$apiUrl}/authenticate", [
            'username' => $username,
            'password' => $password,
        ]);

        if (!$authResponse->successful()) {
            throw new \Exception('Authentication failed');
        }

        $authData = $authResponse->json();
        $sessionId = $authData['sessionId'] ?? '';
        
        if (empty($sessionId)) {
            throw new \Exception('No session token received');
        }

        // Click to dial (session cookie is now in jar)
        $response = $client->timeout(30)->post("{$apiUrl}/call/clicktodial", [
            'customerId' => $customerId,
            'number' => $callerNumber,
            'destinationNumber' => $destinationNumber,
            'callerId' => $callerId,
        ]);

        if (!$response->successful()) {
            $error = $response->json()['errorMessage'] ?? 'Unknown error';
            throw new \Exception("Phonero API error: {$error}");
        }

        return $response->json();
    }
}
