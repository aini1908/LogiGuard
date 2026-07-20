<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('iso_code', 3)->unique();
            $table->string('country_code', 5)->unique()->nullable();
            $table->string('name');
            $table->string('currency_code', 5)->nullable();
            $table->string('region')->nullable();
            $table->decimal('latitude', 10, 8)->nullable(); // Dibikin nullable dulu biar seeder awal aman
            $table->decimal('longitude', 11, 8)->nullable(); // Dibikin nullable dulu biar seeder awal aman
            
            // 🔥 INDIKATOR TAMBAHAN UNTUK RISK SCORING ENGINE (WORLD BANK DATA)
            $table->double('inflation_rate')->nullable();
            $table->bigInteger('gdp_nominal')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};