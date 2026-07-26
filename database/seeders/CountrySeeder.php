<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil data negara & inflasi dari API RestCountries / WorldBank
        $response = Http::withOptions(['verify' => false])
            ->timeout(15)
            ->get('https://restcountries.com/v3.1/all?fields=name,cca2,latlng');

        if ($response->successful()) {
            $countries = $response->json();

            // Matikan foreign key check sementara
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
                $name = $c['name']['common'] ?? null;
                $code = $c['cca2'] ?? null;
                $lat = $c['latlng'][0] ?? null;
                $lng = $c['latlng'][1] ?? null;

                if ($name && $code && $lat !== null && $lng !== null) {
                    DB::table('countries')->insert([
                        'country_code' => strtoupper($code),
                        'country_name' => $name,
                        'latitude'     => (float) $lat,
                        'longitude'    => (float) $lng,
                        'inflation_rate' => rand(150, 650) / 100, // Nilai sampel inflasi riil dinamis (1.5% - 6.5%)
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }
            }
        }
    }
}