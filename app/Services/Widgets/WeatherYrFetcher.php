<?php

namespace App\Services\Widgets;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WeatherYrFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'weather.yr';
    
    private const YR_API_URL = 'https://api.met.no/weatherapi/locationforecast/2.0/compact';
    private const DEFAULT_LAT = 59.4344; // Moss, Østfold
    private const DEFAULT_LON = 10.6574;
    
    protected function fetchData(): array
    {
        try {
            $lat = config('widgets.weather.latitude', self::DEFAULT_LAT);
            $lon = config('widgets.weather.longitude', self::DEFAULT_LON);
            
            // Cache for 30 minutes (Yr.no requests max 20 requests/sec)
            $cacheKey = "weather_yr_{$lat}_{$lon}";
            
            $data = Cache::remember($cacheKey, 1800, function () use ($lat, $lon) {
                return $this->fetchFromYr($lat, $lon);
            });
            
            return $data;
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'status' => 'unavailable',
                'timestamp' => now()->toIso8601String(),
            ];
        }
    }

    private function fetchFromYr(float $lat, float $lon): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Smartesider Dashboard/1.0 (terje@smartesider.no)',
            ])->timeout(10)->get(self::YR_API_URL, [
                'lat' => $lat,
                'lon' => $lon,
            ]);

            if (!$response->successful()) {
                throw new \Exception('Yr.no API returned error: ' . $response->status());
            }

            $data = $response->json();
            
            // Extract current weather (first timeseries entry)
            $current = $data['properties']['timeseries'][0] ?? null;
            
            if (!$current) {
                throw new \Exception('No weather data available');
            }

            $instant = $current['data']['instant']['details'];
            $next1h = $current['data']['next_1_hours']['summary'] ?? null;
            $next1hDetails = $current['data']['next_1_hours']['details'] ?? null;
            
            return [
                'location' => $this->getLocationName($lat, $lon),
                'temperature' => round($instant['air_temperature'], 1),
                'feels_like' => $this->calculateFeelsLike($instant),
                'humidity' => round($instant['relative_humidity'], 0),
                'wind_speed' => round($instant['wind_speed'], 1),
                'wind_direction' => $this->getWindDirection($instant['wind_from_direction'] ?? 0),
                'precipitation' => $next1hDetails['precipitation_amount'] ?? 0,
                'condition' => $next1h['symbol_code'] ?? 'unknown',
                'condition_text' => $this->getConditionText($next1h['symbol_code'] ?? ''),
                'updated_at' => $current['time'],
                'status' => 'ok',
                'timestamp' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            throw new \Exception('Failed to fetch from Yr.no: ' . $e->getMessage());
        }
    }

    private function calculateFeelsLike(array $instant): float
    {
        $temp = $instant['air_temperature'];
        $windSpeed = $instant['wind_speed'] ?? 0;
        
        // Simple wind chill calculation
        if ($temp < 10 && $windSpeed > 1.3) {
            $windChill = 13.12 + 0.6215 * $temp - 11.37 * pow($windSpeed * 3.6, 0.16) + 0.3965 * $temp * pow($windSpeed * 3.6, 0.16);
            return round($windChill, 1);
        }
        
        return round($temp, 1);
    }

    private function getWindDirection(float $degrees): string
    {
        $directions = ['N', 'NØ', 'Ø', 'SØ', 'S', 'SV', 'V', 'NV'];
        $index = round($degrees / 45) % 8;
        return $directions[$index];
    }

    private function getConditionText(string $code): string
    {
        $conditions = [
            'clearsky' => 'Klarvær',
            'fair' => 'Lettskyet',
            'partlycloudy' => 'Delvis skyet',
            'cloudy' => 'Skyet',
            'rain' => 'Regn',
            'lightrain' => 'Lett regn',
            'heavyrain' => 'Kraftig regn',
            'snow' => 'Snø',
            'fog' => 'Tåke',
            'sleet' => 'Sludd',
        ];

        foreach ($conditions as $key => $text) {
            if (stripos($code, $key) !== false) {
                return $text;
            }
        }

        return ucfirst(str_replace('_', ' ', $code));
    }

    private function getLocationName(float $lat, float $lon): string
    {
        // Check if custom location name is set in config/.env
        $configLocation = config('widgets.weather.location', env('WEATHER_LOCATION'));
        
        if ($configLocation) {
            return $configLocation;
        }
        
        // Fallback: Default locations
        $locations = [
            '59.4344,10.6574' => 'Moss, Østfold',
            '59.9139,10.7522' => 'Oslo',
            '60.3913,5.3221' => 'Bergen',
            '63.4305,10.3951' => 'Trondheim',
        ];

        $key = round($lat, 2) . ',' . round($lon, 2);
        
        foreach ($locations as $coords => $name) {
            if (abs($lat - explode(',', $coords)[0]) < 0.1 && abs($lon - explode(',', $coords)[1]) < 0.1) {
                return $name;
            }
        }

        return "Lat {$lat}, Lon {$lon}";
    }
}
