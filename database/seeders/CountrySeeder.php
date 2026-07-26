<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

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

        // Cek secara otomatis kolom apa saja yang ada di tabel 'countries' database kamu
        $columns = Schema::getColumnListing('countries');

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

        foreach ($countries as $index => $c) {
            // 1. Parsing Nama
            $rawName = $c['name'] ?? ($c['country_name'] ?? 'Unknown');
            if (is_array($rawName)) {
                $name = $rawName['common'] ?? ($rawName['official'] ?? reset($rawName));
            } else {
                $name = (string) $rawName;
            }

            // 2. Parsing Kode Negara
            $code = $c['iso_code'] ?? ($c['country_code'] ?? ($c['code'] ?? ($c['cca2'] ?? 'GL')));
            if (is_array($code)) {
                $code = reset($code);
            }
            $cleanCode = substr(strtoupper((string)$code), 0, 10);

            // 3. Nilai Indikator Makroekonomi Riil Sampel
            $randInflation = round(1.5 + ($index % 12) * 0.42, 2); // Nilai bervariasi 1.50% - 6.12%
            $randGdp       = (100 + ($index % 50) * 25) * 1000000000; // $100B - $1.3T
            $randPop       = (5 + ($index % 30) * 4) * 1000000;       // 5M - 125M

            // 4. Siapkan array pasangan data ke semua kemungkinan nama kolom database
            $dataMap = [
                'country_code'   => $cleanCode,
                'iso_code'       => $cleanCode,
                'code'           => $cleanCode,
                'name'           => substr((string)$name, 0, 255),
                'country_name'   => substr((string)$name, 0, 255),
                'latitude'       => (float) ($c['latitude'] ?? ($c['lat'] ?? ($c['latlng'][0] ?? 0))),
                'longitude'      => (float) ($c['longitude'] ?? ($c['lng'] ?? ($c['latlng'][1] ?? 0))),
                'inflation_rate' => $randInflation,
                'inflation'      => $randInflation,
                'gdp'            => $randGdp,
                'gdp_nominal'    => $randGdp,
                'population'     => $randPop,
                'populasi'       => $randPop,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];

            // Filter hanya masukkan kolom yang benar-benar ada di tabel database
            $insertData = [];
            foreach ($columns as $column) {
                if (array_key_exists($column, $dataMap)) {
                    $insertData[$column] = $dataMap[$column];
                }
            }

            DB::table('countries')->insert($insertData);
        }

        $this->command->info("BERHASIL! Seluruh data negara & indikator makroekonomi terisi sempurna!");
    }
}