<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_category_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_price_series_id')->constrained('event_price_series')->cascadeOnDelete();
            $table->foreignId('event_category_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 14, 2)->default(0);
            $table->timestamps();
            $table->unique(['event_price_series_id', 'event_category_id'], 'series_category_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_category_prices');
    }
};
