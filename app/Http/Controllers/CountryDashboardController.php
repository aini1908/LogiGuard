<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\services\DynamicDataService;
use Illuminate\support\Facades\DB;

class CountryDashboardController extends Controller
{
    protected $dataService;

    // Inject Service Multi-API yang kita buat di Fase 2 kemarin
    public function __construct(DynamicDataService $dataService)
    {
        $this->dataService = $dataService;
    }

    public function getDetail($code)
    {
        // 1. Ambil data koordinat negara dari database lokal berdasarkan kode negaranya
        // Jika tabel local menggunakan ID angka, kita cari berdasarkan kode atau fallback koordinat
        $country = DB::table('countries')->where('country_code', $code)->first();
        
        // Koordinat default jika data spasial di database lokal belum lengkap (contoh: pusat peta)
        $lat = $country->latitude ?? -25.2744; 
        $lng = $country->longitude ?? 133.7751;
        $countryName = $country->name ?? 'Global Target';

        // 2. Tembak World Bank API secara real-time via Service
        $economicData = $this->dataService->getEconomicData($code);

        // 3. Tembak Open-Meteo API secara real-time via Service
        $weatherData = $this->dataService->getWeatherData($lat, $lng);

        // 4. Kembalikan data gabungan multi-API dalam format JSON ke Frontend
        return response()->json([
            'status' => 'success',
            'data' => [
                'country_name' => $countryName,
                'country_code' => $code,
                'gdp'          => $economicData['gdp'],
                'inflation'    => $economicData['inflation_rate'],
                'population'   => $economicData['population'],
                'weather'      => [
                    'temperature'   => $weatherData['temperature'],
                    'wind_speed'    => $weatherData['wind_speed'],
                    'precipitation' => $weatherData['precipitation'],
                    'weather_code'  => $weatherData['weather_code']
                ]
            ]
        ], 200);
    }
}
