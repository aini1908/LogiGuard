<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FetchGlobalPortsCommand extends Command
{
    protected $signature = 'ports:fetch';
    protected $description = 'Menyedot ribuan data pelabuhan maritim di seluruh dunia secara otomatis dari OpenStreetMap API';

    public function handle()
    {
        $this->info('Menghubungi server global Overpass API (OpenStreetMap)...');
        $this->info('Proses ini mengunduh ribuan data pelabuhan seluruh dunia, mohon tunggu sebentar...');

        // Query Overpass untuk menarik semua objek yang ditandai sebagai pelabuhan komersial/logistik (harbour=commercial)
        $query = '[out:json][timeout:90];node["industrial"="port"];out body;';
        $apiUrl = "https://overpass-api.de/api/interpreter?data=" . urlencode($query);

        // Menggunakan bypass SSL MacBook/MAMP via native stream context
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
            $this->error('Gagal terhubung ke server OpenStreetMap. Coba beberapa saat lagi.');
            return 1;
        }

        $data = json_decode($rawJson, true);
        $elements = $data['elements'] ?? [];

        if (empty($elements)) {
            $this->error('Data pelabuhan tidak ditemukan atau kosong.');
            return 1;
        }

        // Matikan proteksi foreign key database agar proses pembersihan lancar
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        DB::table('ports')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

        $insertedCount = 0;

        foreach ($elements as $element) {
            $tags = $element['tags'] ?? [];
            $name = $tags['name'] ?? ($tags['operator'] ?? null);
            
            // Ekstrak koordinat
            $lat = $element['lat'] ?? null;
            $lng = $element['lon'] ?? null;
            
            // Ekstrak kode negara secara kasar dari tag, atau default ke 'GL' (Global) jika tidak ada
            $countryCode = isset($tags['addr:country']) ? substr(strtoupper($tags['addr:country']), 0, 2) : 'GL';
            $portCode = 'PRT' . ($element['id'] ?? $insertedCount);

            if ($name && $lat !== null && $lng !== null) {
                // Batasi panjang string agar sesuai kapasitas struktur kolom database kamu
                DB::table('ports')->insert([
                    'port_code'    => substr($portCode, 0, 10),
                    'name'         => substr($name, 0, 255),
                    'country_code' => $countryCode,
                    'latitude'     => $lat,
                    'longitude'    => $lng,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
                $insertedCount++;
            }
        }

        $this->info("BOOM! PANDANGAN DUNIA TERBUKA! Berhasil otomatis memasukkan {$insertedCount} data pelabuhan komersial ke database!");
        return 0;
    }
}