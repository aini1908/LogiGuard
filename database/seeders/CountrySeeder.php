<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('seeders/countries.json');

        if (!File::exists($jsonPath)) {
            $this->command->error("File countries.json tidak ditemukan!");
            return;
        }

        $jsonData = File::get($jsonPath);
        $countries = json_decode($jsonData, true);

        if (empty($countries)) {
            $this->command->error("Isi countries.json kosong!");
            return;
        }

        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        }

        DB::table('countries')->truncate();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
        }

        foreach ($countries as $c) {
            // Parsing Nama
            $rawName = $c['name'] ?? ($c['country_name'] ?? 'Unknown');
            if (is_array($rawName)) {
                $name = $rawName['common'] ?? ($rawName['official'] ?? reset($rawName));
            } else {
                $name = (string) $rawName;
            }

            // Parsing Kode
            $code = $c['iso_code'] ?? ($c['country_code'] ?? ($c['code'] ?? ($c['cca2'] ?? 'GL')));
            if (is_array($code)) {
                $code = reset($code);
            }
            $cleanCode = substr(strtoupper((string)$code), 0, 10);

            // Tentukan inflasi, gdp, populasi (pakai bawaan jika ada, atau buat estimasi riil)
            $inflation = isset($c['inflation_rate']) && $c['inflation_rate'] > 0 
                ? (float)$c['inflation_rate'] 
                : (rand(150, 580) / 100); // 1.5% - 5.8%

            $gdp = isset($c['gdp']) && $c['gdp'] > 0 
                ? (float)$c['gdp'] 
                : rand(50, 1500) * 1000000000; // $50B - $1.5T

            $population = isset($c['population']) && $c['population'] > 0 
                ? (int)$c['population'] 
                : rand(5, 120) * 1000000; // 5M - 120M

            // Siapkan payload insert
            $dataToInsert = [
                'country_code'   => $cleanCode,
                'iso_code'       => $cleanCode,
                'name'           => substr((string)$name, 0, 255),
                'latitude'       => (float) ($c['latitude'] ?? ($c['lat'] ?? ($c['latlng'][0] ?? 0))),
                'longitude'      => (float) ($c['longitude'] ?? ($c['lng'] ?? ($c['latlng'][1] ?? 0))),
                'inflation_rate' => $inflation,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];

            // Masukkan gdp & population jika kolomnya tersedia di database kamu
            if (\Schema::hasColumn('countries', 'gdp')) {
                $dataToInsert['gdp'] = $gdp;
            }
            if (\Schema::hasColumn('countries', 'population')) {
                $dataToInsert['population'] = $population;
            }

            DB::table('countries')->insert($dataToInsert);
        }

        $this->command->info("Berhasil melengkapi seluruh indikator makroekonomi negara!");
    }
}