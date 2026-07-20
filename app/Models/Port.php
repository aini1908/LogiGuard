<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    use HasFactory;

    // Karena nama tabel di databasemu jamak (ports), pastikan dia mengunci nama tabel ini
    protected $table = 'ports';

    // Izinkan semua kolom diisi secara massal dari JSON
    protected $guarded = [];
}