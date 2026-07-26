<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FetchGlobalCountriesCommand extends Command
{
    protected $signature = 'ports:fetch';
    protected $description = 'Menyedot data riil pelabuhan maritim global dari dataset terverifikasi Natural Earth via CDN stabil';

    public function handle()
    {
        $this->info('Menghubungi server CDN dataset pelabuhan maritim global...');
        $this->info('Mengunduh data riil pelabuhan, mohon tunggu sebentar...');

        // Endpoint CDN Dataset GeoJSON Pelabuhan Resmi Natural Earth (100% Aktif & Stabil)
        $cdnUrl = "https://raw.githubusercontent.com/nvkelso/natural-earth-vector/master/geojson/ne_10m_ports.geojson";

        try {
            $response = Http::withOptions(['verify' => false])
                ->timeout(15)
                ->get($cdnUrl);

            if ($response->failed()) {
                $this->error('Gagal terhubung ke CDN Data Pelabuhan. Status: ' . $response->status());
                return 1;
            }

            $geojson = $response->json();
            $features = $geojson['features'] ?? [];

            if (empty($features)) {
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

            foreach ($features as $feature) {
                $props = $feature['properties'] ?? [];
                $geometry = $feature['geometry'] ?? [];
                $coords = $geometry['coordinates'] ?? [];

                $name = $props['name'] ?? ($props['name_en'] ?? null);
                // Mengambil nama/kode lokasi dari properti GeoJSON
                $countryCode = $props['natlscale'] ?? 'GL';
                
                // GeoJSON menggunakan format [longitude, latitude]
                $lng = $coords[0] ?? null;
                $lat = $coords[1] ?? null;

                if ($name && $lat !== null && $lng !== null && is_numeric($lat) && is_numeric($lng)) {
                    DB::table('ports')->insert([
                        'port_name'    => substr($name, 0, 255),
                        'country_code' => substr(strtoupper((string)$countryCode), 0, 2),
                        'country_name' => substr($name, 0, 100),
                        'latitude'     => (float) $lat,
                        'longitude'    => (float) $lng,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                    $insertedCount++;
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