<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Country extends Model
{
    use HasFactory;
    protected $table = 'countries';
    protected $fillable = [
        'country_code',
        'iso_code', // Mendaftarkan kolom ini agar bisa diisi otomatis oleh API
        'name',
        'currency_code',
        'region',
        'latitude',
        'longitude',
        'inflation_rate', // Daftarkan juga kolom ini untuk menampung data World Bank
        'gdp_nominal'     // Daftarkan juga kolom ini untuk menampung data World Bank
    ];
}
