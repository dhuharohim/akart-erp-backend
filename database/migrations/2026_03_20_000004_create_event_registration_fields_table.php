<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_registration_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('field_name', 100);
            $table->string('field_label');
            $table->string('field_type', 50)->default('text');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->json('options')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->index(['event_id', 'is_enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registration_fields');
    }
};
