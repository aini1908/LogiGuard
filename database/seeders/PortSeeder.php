<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PortSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ports')->insert([
            [
                'port_name' => 'Tanjung Priok',
                'country_code' => 'ID',
                'country_name' => 'Indonesia',
                'latitude' => -6.1033,
                'longitude' => 106.8792,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'port_name' => 'Port of Hamburg',
                'country_code' => 'DE',
                'country_name' => 'Germany',
                'latitude' => 53.5422,
                'longitude' => 9.9388,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'port_name' => 'Port of Shanghai',
                'country_code' => 'CN',
                'country_name' => 'China',
                'latitude' => 31.2222,
                'longitude' => 121.5435,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'port_name' => 'Port of Melbourne',
                'country_code' => 'AU',
                'country_name' => 'Australia',
                'latitude' => -37.8301,
                'longitude' => 144.9124,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}