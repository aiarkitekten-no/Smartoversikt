<?php

namespace App\Services\Widgets;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WeatherPowerPriceFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'weather.power-price';
    
    private const NORDPOOL_API = 'https://www.nordpoolgroup.com/api/marketdata/page/10';
    
    protected function fetchData(): array
    {
        try {
            // Cache for 1 hour
            $data = Cache::remember('power_prices', 3600, function () {
                return $this->fetchPowerPrices();
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

    private function fetchPowerPrices(): array
    {
        try {
            // Simplified approach: Mock data for now since Nordpool API requires auth
            // In production, this would fetch from Nordpool or Tibber API
            
            $currentHour = (int) now()->format('H');
            
            // Generate realistic mock prices (øre/kWh)
            $basePrice = 80; // Base price in øre
            $variation = sin($currentHour / 24 * 2 * M_PI) * 30; // Daily variation
            $currentPrice = round($basePrice + $variation + rand(-10, 10), 2);
            
            // Generate hourly prices for today
            $hourlyPrices = [];
            for ($hour = 0; $hour < 24; $hour++) {
                $variation = sin($hour / 24 * 2 * M_PI) * 30;
                $hourlyPrices[] = [
                    'hour' => sprintf('%02d:00', $hour),
                    'price' => round($basePrice + $variation + rand(-5, 5), 2),
                ];
            }
            
            // Find min/max
            $prices = array_column($hourlyPrices, 'price');
            $minPrice = min($prices);
            $maxPrice = max($prices);
            $avgPrice = round(array_sum($prices) / count($prices), 2);
            
            return [
                'current_price' => $currentPrice,
                'current_hour' => sprintf('%02d:00', $currentHour),
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'avg_price' => $avgPrice,
                'unit' => 'øre/kWh',
                'hourly' => $hourlyPrices,
                'recommendation' => $this->getRecommendation($currentPrice, $avgPrice),
                'status' => 'ok',
                'note' => 'Demo data - integrate with Nordpool/Tibber API',
                'timestamp' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            throw new \Exception('Failed to fetch power prices: ' . $e->getMessage());
        }
    }

    private function getRecommendation(float $current, float $avg): string
    {
        $diff = (($current - $avg) / $avg) * 100;
        
        if ($diff > 20) {
            return '🔴 Dyrt nå - vent med strømkrevende oppgaver';
        } elseif ($diff > 5) {
            return '🟡 Over gjennomsnitt';
        } elseif ($diff < -20) {
            return '🟢 Billig nå - god tid å bruke strøm';
        } elseif ($diff < -5) {
            return '🟢 Under gjennomsnitt';
        }
        
        return '⚪ Normal pris';
    }
}
