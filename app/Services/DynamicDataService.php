<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DynamicDataService
{
    /**
     * Mengambil Data Makroekonomi dari World Bank API (GDP, Inflasi, Populasi)
     */
    public function getEconomicData($countryCode)
    {
        // Default tahun pemantauan (ambil data tahun terbaru yang stabil)
        $year = 2024; 
        $data = [
            'gdp' => null,
            'inflation_rate' => null,
            'population' => null
        ];

        try {
            // 1. Ambil GDP Nominal
            $gdpResponse = Http::get("https://api.worldbank.org/v2/country/{$countryCode}/indicator/NY.GDP.MKTP.CD?date={$year}&format=json");
            if ($gdpResponse->successful() && isset($gdpResponse->json()[1][0]['value'])) {
                $data['gdp'] = $gdpResponse->json()[1][0]['value'];
            }

            // 2. Ambil Inflasi Tingkat Konsumen (%)
            $inflationResponse = Http::get("https://api.worldbank.org/v2/country/{$countryCode}/indicator/FP.CPI.TOTL.ZG?date={$year}&format=json");
            if ($inflationResponse->successful() && isset($inflationResponse->json()[1][0]['value'])) {
                $data['inflation_rate'] = $inflationResponse->json()[1][0]['value'];
            }

            // 3. Ambil Total Populasi
            $popResponse = Http::get("https://api.worldbank.org/v2/country/{$countryCode}/indicator/SP.POP.TOTL?date={$year}&format=json");
            if ($popResponse->successful() && isset($popResponse->json()[1][0]['value'])) {
                $data['population'] = $popResponse->json()[1][0]['value'];
            }

        } catch (\Exception $e) {
            Log::error("Gagal mengambil data World Bank untuk {$countryCode}: " . $e->getMessage());
        }

        return $data;
    }

    /**
     * Mengambil Data Cuaca Ekstrem dari Open-Meteo API secara Real-Time
     */
    public function getWeatherData($latitude, $longitude)
    {
        $weatherData = [
            'temperature' => null,
            'precipitation' => null,
            'wind_speed' => null,
            'weather_code' => null
        ];

        try {
            // Menembak endpoint Open-Meteo tanpa API Key sesuai spesifikasi tugas
            $response = Http::get("https://api.open-meteo.com/v1/forecast", [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current_weather' => true
            ]);

            if ($response->successful() && isset($response->json()['current_weather'])) {
                $current = $response->json()['current_weather'];
                $weatherData['temperature'] = $current['temperature'] ?? null;
                $weatherData['wind_speed'] = $current['windspeed'] ?? null;
                $weatherData['weather_code'] = $current['weathercode'] ?? null;
                // Mengambil taksiran curah hujan dari response jika tersedia
                $weatherData['precipitation'] = $response->json()['current']['precipitation'] ?? 0.0;
            }
        } catch (\Exception $e) {
            Log::error("Gagal mengambil data Open-Meteo untuk koordinat [{$latitude}, {$longitude}]: " . $e->getMessage());
        }

        return $weatherData;
    }
}