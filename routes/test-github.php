<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

Route::get('/test-github/{username}/{token}', function ($username, $token) {
    $headers = [
        'Authorization' => "Bearer {$token}",
        'Accept' => 'application/vnd.github.v3+json',
        'User-Agent' => 'Smartesider-Dashboard',
    ];
    
    // Test user endpoint
    $userResponse = Http::withHeaders($headers)->get("https://api.github.com/users/{$username}");
    
    echo "<h1>GitHub API Test</h1>";
    echo "<h2>User Data</h2>";
    echo "<pre>Status: " . $userResponse->status() . "</pre>";
    echo "<pre>" . json_encode($userResponse->json(), JSON_PRETTY_PRINT) . "</pre>";
    
    // Test events endpoint
    $eventsResponse = Http::withHeaders($headers)->get("https://api.github.com/users/{$username}/events");
    
    echo "<h2>Events Data</h2>";
    echo "<pre>Status: " . $eventsResponse->status() . "</pre>";
    echo "<pre>Count: " . count($eventsResponse->json()) . "</pre>";
    echo "<pre>" . json_encode(array_slice($eventsResponse->json(), 0, 3), JSON_PRETTY_PRINT) . "</pre>";
});
