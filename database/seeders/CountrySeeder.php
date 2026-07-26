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
            // Ambil nama negara (baik jika berbentuk string maupun array/object)
            $rawName = $c['name'] ?? ($c['country_name'] ?? 'Unknown');
            if (is_array($rawName)) {
                $name = $rawName['common'] ?? ($rawName['official'] ?? reset($rawName));
            } else {
                $name = (string) $rawName;
            }

            // Ambil kode negara
            $code = $c['country_code'] ?? ($c['code'] ?? ($c['cca2'] ?? 'GL'));
            if (is_array($code)) {
                $code = reset($code);
            }

            DB::table('countries')->insert([
                'country_code'   => substr(strtoupper((string)$code), 0, 10),
                'name'           => substr((string)$name, 0, 255),
                'latitude'       => (float) ($c['latitude'] ?? ($c['lat'] ?? ($c['latlng'][0] ?? 0))),
                'longitude'      => (float) ($c['longitude'] ?? ($c['lng'] ?? ($c['latlng'][1] ?? 0))),
                'inflation_rate' => (float) ($c['inflation_rate'] ?? ($c['inflation'] ?? 0)),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }
}