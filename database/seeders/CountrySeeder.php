<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    
    public function run(): void
    {
        $countries = [
            [
                'country_code' => 'ID',
                'name' => 'Indonesia',
                'currency_code' => 'IDR',
                'region' => 'Asia',
                'latitude' => -0.789275,
                'longitude' => 113.921327,
            ],
            [
                'country_code' => 'DE',
                'name' => 'Germany',
                'currency_code' => 'EUR',
                'region' => 'Europe',
                'latitude' => 51.165691,
                'longitude' => 10.451526,
            ],
            [
                'country_code' => 'CN',
                'name' => 'China',
                'currency_code' => 'CNY',
                'region' => 'Asia',
                'latitude' => 35.861660,
                'longitude' => 104.195397,
            ],
            [
                'country_code' => 'AU',
                'name' => 'Australia',
                'currency_code' => 'AUD',
                'region' => 'Oceania',
                'latitude' => -25.274398,
                'longitude' => 133.775136,
            ]

            ];
            foreach ($countries as $country) {
                DB::table('countries')->insertOrIgnore(array_merge($country, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
    }
}
