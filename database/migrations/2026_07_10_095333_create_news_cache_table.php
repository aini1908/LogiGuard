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
    Schema::create('news_cache', function (Blueprint $table) {
        $table->id();
        $table->string('country_code', 5); // Berelasi dengan kode teks negara (contoh: 'AU')
        $table->string('title');
        $table->string('source_name')->nullable();
        $table->text('description')->nullable();
        $table->text('url')->nullable();
        $table->string('category')->default('logistics'); // logistics, trade, shipping, economy
        $table->timestamp('published_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_cache');
    }
};
