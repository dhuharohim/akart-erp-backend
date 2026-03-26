<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_series_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_series_id')->constrained('event_series')->cascadeOnDelete();
            $table->foreignId('event_staff_id')->constrained('event_staff')->cascadeOnDelete();
            $table->string('label')->nullable(); // e.g. "Registration Help", "Technical Support"
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['event_series_id', 'event_staff_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_series_contacts');
    }
};
