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
    Schema::create('economic_indicators', function (Blueprint $table) {
        $table->id();
        $table->string('country_code', 5);
        $table->integer('year');
        $table->double('gdp', 20, 2)->nullable(); // Nilai GDP nominal
        $table->double('inflation_rate', 8, 2)->nullable(); // Nilai inflasi %
        $table->bigInteger('population')->nullable(); // Total populasi
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('economic_indicators');
    }
};
