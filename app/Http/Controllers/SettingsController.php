<?php

namespace App\Http\Controllers;

use App\Models\MailAccount;
use App\Models\RssFeed;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $imapAccounts = MailAccount::imap()->orderBy('name')->get();
        $smtpAccounts = MailAccount::smtp()->orderBy('name')->get();
        $rssFeeds = RssFeed::orderBy('category')->orderBy('name')->get();
        
        $weatherSettings = [
            'lat' => env('WEATHER_LAT', 59.4344),
            'lon' => env('WEATHER_LON', 10.6574),
            'location' => env('WEATHER_LOCATION', 'Moss, Østfold'),
        ];
        
        return view('settings.index', compact('imapAccounts', 'smtpAccounts', 'rssFeeds', 'weatherSettings'));
    }

    public function storeMailAccount(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:imap,smtp',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'encryption' => 'required|in:ssl,tls,none',
            'validate_cert' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['validate_cert'] = $request->has('validate_cert');
        $validated['is_active'] = $request->has('is_active');

        MailAccount::create($validated);

        return redirect()->route('settings.index')
            ->with('success', 'E-postkonto lagt til!');
    }

    public function updateMailAccount(Request $request, MailAccount $mailAccount)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string|max:255',
            'encryption' => 'required|in:ssl,tls,none',
            'validate_cert' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['validate_cert'] = $request->has('validate_cert');
        $validated['is_active'] = $request->has('is_active');

        // Only update password if provided
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $mailAccount->update($validated);

        return redirect()->route('settings.index')
            ->with('success', 'E-postkonto oppdatert!');
    }

    public function destroyMailAccount(MailAccount $mailAccount)
    {
        $mailAccount->delete();

        return redirect()->route('settings.index')
            ->with('success', 'E-postkonto slettet!');
    }

    public function updateWeather(Request $request)
    {
        $validated = $request->validate([
            'weather_lat' => 'required|numeric|between:-90,90',
            'weather_lon' => 'required|numeric|between:-180,180',
            'weather_location' => 'required|string|max:255',
        ]);

        $this->updateEnvFile($validated);

        return redirect()->route('settings.index')
            ->with('success', 'Værinnstillinger lagret!');
    }

    private function updateEnvFile(array $data): void
    {
        $envPath = base_path('.env');
        
        if (!file_exists($envPath)) {
            return;
        }

        $envContent = file_get_contents($envPath);

        $updates = [
            'WEATHER_LAT' => $data['weather_lat'] ?? 59.4344,
            'WEATHER_LON' => $data['weather_lon'] ?? 10.6574,
            'WEATHER_LOCATION' => $data['weather_location'] ?? 'Moss, Østfold',
        ];

        foreach ($updates as $key => $value) {
            // Wrap in quotes if value contains spaces
            if (is_string($value) && (empty($value) || strpos($value, ' ') !== false)) {
                $quotedValue = '"' . str_replace('"', '\\"', $value) . '"';
            } else {
                $quotedValue = $value;
            }
            
            $pattern = "/^{$key}=.*/m";
            
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, "{$key}={$quotedValue}", $envContent);
            }
        }

        file_put_contents($envPath, $envContent);

        // Clear config cache
        if (app()->environment('production')) {
            \Artisan::call('config:clear');
            \Artisan::call('config:cache');
        }
    }

    public function storeRssFeed(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'category' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'refresh_interval' => 'nullable|integer|min:60|max:3600',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['refresh_interval'] = $validated['refresh_interval'] ?? 600;

        RssFeed::create($validated);

        return redirect()->route('settings.index')
            ->with('success', 'RSS-feed lagt til!');
    }

    public function updateRssFeed(Request $request, RssFeed $rssFeed)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'category' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'refresh_interval' => 'nullable|integer|min:60|max:3600',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $rssFeed->update($validated);

        return redirect()->route('settings.index')
            ->with('success', 'RSS-feed oppdatert!');
    }

    public function destroyRssFeed(RssFeed $rssFeed)
    {
        $rssFeed->delete();

        return redirect()->route('settings.index')
            ->with('success', 'RSS-feed slettet!');
    }
}

