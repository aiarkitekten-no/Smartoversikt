<?php

namespace App\Services\Widgets;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class WeatherPowerPriceFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'weather.power-price';
    
    // Konfigurasjon (Moss/Oslo-området)
    private const AREA = 'NO1';  // NO1 = Oslo/Øst-Norge
    private const VAT_RATE = 1.25;  // 25% mva (NO1)
    private const MARKUP_ORE = 6.0;  // Fortum påslag i øre/kWh
    private const MONTHLY_FEE = 0.0;  // Fastbeløp per måned
    private const EST_MONTHLY_KWH = 0.0;  // Estimert forbruk for å fordele fastbeløp
    
    protected function fetchData(): array
    {
        try {
            // Cache for 1 hour
            $data = Cache::remember('power_prices_' . self::AREA, 3600, function () {
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
            $today = Carbon::now('Europe/Oslo');
            $dateStr = $today->format('Y/m-d');
            
            // Hent priser fra hvakosterstrommen.no API
            $url = "https://www.hvakosterstrommen.no/api/v1/prices/{$dateStr}_" . self::AREA . ".json";
            
            $response = Http::timeout(20)->get($url);
            
            if (!$response->successful()) {
                throw new \Exception('API request failed: ' . $response->status());
            }
            
            $apiData = $response->json();
            
            // Beregn per-kWh kostnad for fastbeløp
            $perKwhMonthlyFee = 0.0;
            if (self::MONTHLY_FEE > 0 && self::EST_MONTHLY_KWH > 0) {
                $perKwhMonthlyFee = self::MONTHLY_FEE / self::EST_MONTHLY_KWH;
            }
            
            // Prosesser timepriser
            $hourlyPrices = [];
            $now = Carbon::now('Europe/Oslo');
            $currentPrice = null;
            
            foreach ($apiData as $row) {
                $baseNoVat = (float) $row['NOK_per_kWh'];  // Grunnpris uten mva
                $withVat = $baseNoVat * self::VAT_RATE;
                $totalFortumish = $withVat + (self::MARKUP_ORE / 100.0) + $perKwhMonthlyFee;
                
                $timeStart = Carbon::parse($row['time_start'])->setTimezone('Europe/Oslo');
                $hour = (int) $timeStart->format('H');
                
                $priceData = [
                    'hour' => sprintf('%02d:00', $hour),
                    'base_no_vat' => round($baseNoVat, 4),
                    'with_vat' => round($withVat, 4),
                    'total_fortumish' => round($totalFortumish, 4),
                    'ore_per_kwh' => round($totalFortumish * 100, 2),  // Konverter til øre
                    'time_start' => $timeStart->toIso8601String(),
                ];
                
                $hourlyPrices[] = $priceData;
                
                // Finn nåværende pris
                $timeEnd = $timeStart->copy()->addHour();
                if ($timeStart <= $now && $now < $timeEnd) {
                    $currentPrice = $priceData;
                }
            }
            
            // Beregn statistikk (basert på Fortum-ish pris i øre)
            $prices = array_column($hourlyPrices, 'ore_per_kwh');
            $minPrice = min($prices);
            $maxPrice = max($prices);
            $avgPrice = round(array_sum($prices) / count($prices), 2);
            
            // Finn når det er billigst/dyrest
            $minHour = null;
            $maxHour = null;
            foreach ($hourlyPrices as $p) {
                if ($p['ore_per_kwh'] == $minPrice) {
                    $minHour = $p['hour'];
                }
                if ($p['ore_per_kwh'] == $maxPrice) {
                    $maxHour = $p['hour'];
                }
            }
            
            return [
                'area' => self::AREA,
                'area_name' => 'Oslo/Øst-Norge',
                'current_price' => $currentPrice ? $currentPrice['ore_per_kwh'] : null,
                'current_hour' => $currentPrice ? $currentPrice['hour'] : null,
                'current_details' => $currentPrice ? [
                    'base_no_vat_nok' => $currentPrice['base_no_vat'],
                    'with_vat_nok' => $currentPrice['with_vat'],
                    'total_nok' => $currentPrice['total_fortumish'],
                ] : null,
                'min_price' => $minPrice,
                'min_hour' => $minHour,
                'max_price' => $maxPrice,
                'max_hour' => $maxHour,
                'avg_price' => $avgPrice,
                'unit' => 'øre/kWh',
                'config' => [
                    'vat_rate' => '25%',
                    'markup_ore' => self::MARKUP_ORE,
                    'monthly_fee' => self::MONTHLY_FEE,
                    'includes' => 'mva + påslag' . ($perKwhMonthlyFee > 0 ? ' + andel fastbeløp' : ''),
                ],
                'hourly' => $hourlyPrices,
                'recommendation' => $this->getRecommendation(
                    $currentPrice ? $currentPrice['ore_per_kwh'] : $avgPrice, 
                    $avgPrice,
                    $minHour,
                    $maxHour
                ),
                'status' => 'ok',
                'timestamp' => $now->toIso8601String(),
            ];
        } catch (\Exception $e) {
            throw new \Exception('Failed to fetch power prices: ' . $e->getMessage());
        }
    }

    private function getRecommendation(float $current, float $avg, ?string $minHour, ?string $maxHour): string
    {
        $diff = (($current - $avg) / $avg) * 100;
        
        if ($diff > 20) {
            return "🔴 Dyrt nå - billigst kl. {$minHour}";
        } elseif ($diff > 5) {
            return "🟡 Over gjennomsnitt - billigst kl. {$minHour}";
        } elseif ($diff < -20) {
            return "🟢 Billig nå - god tid å bruke strøm!";
        } elseif ($diff < -5) {
            return "🟢 Under gjennomsnitt - bra tid";
        }
        
        return "⚪ Normal pris - dyrest kl. {$maxHour}";
    }
}
