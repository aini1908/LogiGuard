<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Kode rute otomatisasi pelabuhan di bawah ini dibungkus dengan komentar 
| agar dinonaktifkan sepenuhnya. Database 4400 pelabuhanmu sekarang sudah aman!
*/

/*
Route::get('/inject-ports-otomatis', function () {
    // 1. Query Overpass untuk menarik semua objek pelabuhan industri/komersial di bumi
    $query = '[out:json][timeout:180];
    (
        node["harbour"~"commercial|public|industrial"];
        way["harbour"~"commercial|public|industrial"];
        node["industrial"="port"];
        way["industrial"="port"];
      );
      out center;';
    $apiUrl = "https://overpass-api.de/api/interpreter?data=" . urlencode($query);

    $streamOptions = [
        "ssl" => [
            "verify_peer" => false,
            "verify_peer_name" => false,
        ],
        "http" => [
            "header" => "User-Agent: LogiGuardShippingApp/1.0\r\n"
        ]
    ];
    $context = stream_context_create($streamOptions);
    $rawJson = @file_get_contents($apiUrl, false, $context);

    if ($rawJson === false) {
        return response()->json(['status' => 'error', 'message' => 'Gagal terhubung ke server OpenStreetMap. Coba lagi.']);
    }

    $data = json_decode($rawJson, true);
    $elements = $data['elements'] ?? [];

    if (empty($elements)) {
        return response()->json(['status' => 'error', 'message' => 'Data pelabuhan kosong.']);
    }

    // 2. Bersihkan tabel pelabuhan lokal dengan mematikan foreign key
    DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
    DB::table('ports')->truncate();
    DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

    $insertedCount = 0;

    // 3. Masukkan ribuan data otomatis (Sudah disesuaikan dengan kolom phpMyAdmin kamu)
    foreach ($elements as $element) {
        $tags = $element['tags'] ?? [];
        $name = $tags['name'] ?? ($tags['operator'] ?? 'Unknown Port');
        
        // Mengambil koordinat secara fleksibel (baik dari node biasa maupun center dari area/way)
        $lat = $element['lat'] ?? ($element['center']['lat'] ?? null);
        $lng = $element['lon'] ?? ($element['center']['lon'] ?? null);
        
        $countryCode = isset($tags['addr:country']) ? substr(strtoupper($tags['addr:country']), 0, 2) : 'GL';
        $countryName = $tags['addr:country'] ?? 'Global Area';

        if ($lat !== null && $lng !== null) {
            DB::table('ports')->insert([
                'port_name'    => substr($name, 0, 255),
                'country_code' => $countryCode,
                'country_name' => substr($countryName, 0, 255),
                'latitude'     => $lat,
                'longitude'    => $lng,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
            $insertedCount++;
        }
    }

    return response()->json([
        'status' => 'success',
        'message' => "BOOM! Sukses total Hurul! Berhasil otomatis memasukkan {$insertedCount} data pelabuhan ke phpMyAdmin tanpa ketik manual!"
    ]);
});
*/

// =========================================================================
// DI BAWAH SINI KAMU BISA MENULIS ROUTE-ROUTE BARU UNTUK DASHBOARD KURS BESOK
// =========================================================================
/*
Route::get('/inject-currency-otomatis', function () {
    // 1. Ambil semua negara dari databasemu
    $countries = DB::table('countries')->get();

    if ($countries->isEmpty()) {
        return response()->json(['status' => 'error', 'message' => 'Tabel countries kamu kosong!']);
    }

    // 2. Bersihkan tabel currency_history lama dengan mematikan foreign key
    DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
    DB::table('currency_history')->truncate();
    DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

    // 3. Ambil data kurs patokan global dari API terpercaya
    $apiUrl = "https://open.er-api.com/v6/latest/USD";
    $rawJson = @file_get_contents($apiUrl, false, stream_context_create(["ssl" => ["verify_peer" => false, "verify_peer_name" => false]]));

    if ($rawJson === false) {
        return response()->json(['status' => 'error', 'message' => 'Gagal terhubung ke server kurs global.']);
    }

    $apiData = json_decode($rawJson, true);
    $rates = $apiData['rates'] ?? [];
    $idrRate = $rates['IDR'] ?? 16000; // Kurs dasar USD ke IDR saat ini

    // Mapping kode mata uang global standar untuk 250 negara
    $commonCurrencies = [
        'US' => 'USD', 'DE' => 'EUR', 'FR' => 'EUR', 'IT' => 'EUR', 'ES' => 'EUR', 'NL' => 'EUR',
        'GB' => 'GBP', 'JP' => 'JPY', 'CN' => 'CNY', 'SG' => 'SGD', 'MY' => 'MYR', 'AU' => 'AUD',
        'IN' => 'INR', 'KR' => 'KRW', 'CA' => 'CAD', 'CH' => 'CHF', 'HK' => 'HKD', 'NZ' => 'NZD',
        'TH' => 'THB', 'PH' => 'PHP', 'VN' => 'VND', 'SA' => 'SAR', 'AE' => 'AED', 'ID' => 'IDR'
    ];

    // Tanggal simulasi mundur 6 bulan ke belakang
    $dates = ['2026-01-12', '2026-02-12', '2026-03-12', '2026-04-12', '2026-05-12', '2026-06-12'];
    $fluctuations = [0.985, 0.992, 1.005, 0.998, 1.012, 1.000];
    
    $insertedCount = 0;

    // 4. Masukkan data tren historis untuk setiap negara secara massal
    foreach ($countries as $country) {
        $cCode = strtoupper($country->country_code);
        $currencyCode = $commonCurrencies[$cCode] ?? ($cCode . 'D');

        // Dapatkan nilai konversi mata uang terhadap Rupiah (IDR)
        $rateToUsd = $rates[$currencyCode] ?? null;
        if ($rateToUsd && $rateToUsd > 0) {
            $currentRateToIdr = $idrRate / $rateToUsd;
        } else {
            // Jika mata uang unik, gunakan acakan fluktuasi USD agar tetap terisi datanya
            $currentRateToIdr = $idrRate * (1 + (abs(crc32($cCode) % 20) - 10) / 100);
            $currencyCode = 'USD';
        }

        // Loop 6 bulan untuk membuat baris data historis (supaya bisa jadi grafik Chart.js)
        foreach ($dates as $index => $date) {
            $calculatedRate = round($currentRateToIdr * $fluctuations[$index], 2);

            DB::table('currency_history')->insert([
                'currency_code' => substr($currencyCode, 0, 10),
                'exchange_rate' => $calculatedRate,
                'recorded_date' => $date,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
            $insertedCount++;
        }
    }

    return response()->json([
        'status' => 'success',
        'message' => "BOOM! Sukses total Hurul! Berhasil otomatis memasukkan {$insertedCount} baris data tren mata uang ke tabel currency_history phpMyAdmin tanpa ketik manual!"
    ]);
});
*/

use App\Http\Controllers\CurrencyImpactController;

Route::get('/api/currency-trend', [CurrencyImpactController::class, 'getCurrencyTrend']);

Route::get('/currency-dashboard', function () {
    // Mengambil daftar negara untuk dipasang di dropdown pilihan nanti
    $countries = DB::table('countries')->orderBy('name', 'asc')->get();
    return view('currency_dashboard', compact('countries'));
});
use App\Http\Controllers\ShippingCalculatorController;
Route::get('/shipping-calculator', [ShippingCalculatorController::class, 'showCalculator']);
Route::post('/api/calculate-shipping', [ShippingCalculatorController::class, 'calculateCost']);