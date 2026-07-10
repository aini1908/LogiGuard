<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CountryController extends Controller
{
    /**
     * GET /api/countries
     * Mengambil data 240+ negara via Mirror CDN API Global yang super stabil
     */
    public function index()
    {
        try {
            // Kita gunakan mirror dataset resmi restcountries v3.1 yang disimpan di CDN global agar anti-redirect
            $response = Http::withoutVerifying()->timeout(15)->get('https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/json/countries.json');

            if ($response->failed()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal terhubung ke data server cadangan global.'
                ], 500);
            }

            $rawCountries = $response->json();
            $formattedCountries = [];

            if (is_array($rawCountries)) {
                foreach ($rawCountries as $country) {
                    // Mapping struktur data dari open dataset global
                    $name = $country['name'] ?? null;
                    $code = $country['iso2'] ?? null; // Kode 2 huruf (ID, DE, US, dll)
                    
                    if (!$name || !$code) {
                        continue;
                    }

                    $currencyCode = $country['currency'] ?? 'USD';
                    $region = $country['region'] ?? 'Global';
                    
                    // Ambil koordinat latitude & longitude
                    $lat = isset($country['latitude']) ? (double)$country['latitude'] : 0.0;
                    $lng = isset($country['longitude']) ? (double)$country['longitude'] : 0.0;

                    $formattedCountries[] = [
                        'id' => $code,
                        'country_code' => $code,
                        'name' => $name,
                        'currency_code' => $currencyCode,
                        'region' => $region,
                        'latitude' => $lat,
                        'longitude' => $lng
                    ];
                }
            }

            // Urutkan seluruh negara dari abjad A sampai Z
            usort($formattedCountries, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Sukses memuat ' . count($formattedCountries) . ' data negara global secara real-time via CDN Engine!',
                'data' => $formattedCountries
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }
}