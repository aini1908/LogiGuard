<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FetchGlobalCountriesCommand extends Command
{
    protected $signature = 'ports:fetch';
    protected $description = 'Menyedot data riil pelabuhan maritim global dari dataset terverifikasi UN/LOCODE & OSM via CDN stabil';

    public function handle()
    {
        $this->info('Menghubungi server CDN dataset pelabuhan maritim global...');
        $this->info('Mengunduh data riil pelabuhan, mohon tunggu sebentar...');

        // Endpoint CDN Dataset Riil Pelabuhan Utama Dunia (UN/LOCODE & OSM Data)
        $apiUrl = "https://raw.githubusercontent.com/datasets/gold-prices/main/data/annual.json"; // contoh fallback aman
        
        // Kita gunakan endpoint JSON dataset pelabuhan riil publik yang ringan & presisi
        $cdnUrl = "https://raw.githubusercontent.com/datasets/port-codes/master/data/port-codes.json";

        try {
            $response = Http::withOptions(['verify' => false])
                ->timeout(15)
                ->get($cdnUrl);

            if ($response->failed()) {
                $this->error('Gagal terhubung ke CDN Data Pelabuhan. Status: ' . $response->status());
                return 1;
            }

            $portsData = $response->json();

            if (empty($portsData)) {
                $this->error('Data pelabuhan kosong.');
                return 1;
            }

            // Pengecekan driver database: Aman untuk SQLite maupun MySQL
            $driver = DB::getDriverName();
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
            }

            DB::table('ports')->truncate();

            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            }

            $insertedCount = 0;

            foreach ($portsData as $port) {
                // Ekstrak properti data riil
                $name = $port['Name'] ?? ($port['name'] ?? null);
                $countryCode = $port['Country'] ?? ($port['country_code'] ?? 'GL');
                $lat = $port['Coordinates'][1] ?? ($port['latitude'] ?? null);
                $lng = $port['Coordinates'][0] ?? ($port['longitude'] ?? null);

                // Jika koordinat berbentuk string "lat, lng"
                if (!$lat && isset($port['Coordinates']) && is_string($port['Coordinates'])) {
                    $coords = explode(',', $port['Coordinates']);
                    $lat = trim($coords[0] ?? '');
                    $lng = trim($coords[1] ?? '');
                }

                if ($name && $lat !== null && $lng !== null && is_numeric($lat) && is_numeric($lng)) {
                    DB::table('ports')->insert([
                        'port_name'    => substr($name, 0, 255),
                        'country_code' => substr(strtoupper($countryCode), 0, 2),
                        'country_name' => substr($countryCode, 0, 100),
                        'latitude'     => (float) $lat,
                        'longitude'    => (float) $lng,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                    $insertedCount++;
                }

                // Batasi maksimum 1.000 pelabuhan utama agar pemrosesan database sangat cepat
                if ($insertedCount >= 1000) {
                    break;
                }
            }

            $this->info("BERHASIL! Berhasil otomatis memasukkan {$insertedCount} data riil pelabuhan maritim dunia ke database!");
            return 0;

        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan saat memproses data: ' . $e->getMessage());
            return 1;
        }
    }
}