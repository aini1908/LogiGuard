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
            DB::table('countries')->insert([
                'country_code'   => $c['country_code'] ?? ($c['code'] ?? 'GL'),
                'country_name'   => $c['country_name'] ?? ($c['name'] ?? 'Unknown'),
                'latitude'       => (float) ($c['latitude'] ?? ($c['lat'] ?? 0)),
                'longitude'      => (float) ($c['longitude'] ?? ($c['lng'] ?? 0)),
                'inflation_rate' => (float) ($c['inflation_rate'] ?? ($c['inflation'] ?? 0)),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }
}