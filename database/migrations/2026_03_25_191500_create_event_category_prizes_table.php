<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_category_prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_series_id')->constrained('event_series')->cascadeOnDelete();
            $table->foreignId('event_category_id')->constrained('event_categories')->cascadeOnDelete();
            $table->unsignedInteger('rank')->default(1);
            $table->string('prize_name');
            $table->decimal('prize_value', 15, 2)->nullable();
            $table->string('prize_note', 500)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['event_series_id', 'event_category_id']);
            $table->index(['event_category_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_category_prizes');
    }
};
