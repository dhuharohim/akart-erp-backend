<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create field groups table
        Schema::create('event_registration_field_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_series_id')->constrained('event_series')->cascadeOnDelete();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. Add field_group_id FK and drop old field_group string
        Schema::table('event_registration_fields', function (Blueprint $table) {
            $table->foreignId('field_group_id')
                ->nullable()
                ->after('event_series_id')
                ->constrained('event_registration_field_groups')
                ->cascadeOnDelete();
            $table->dropColumn('field_group');
        });
    }

    public function down(): void
    {
        Schema::table('event_registration_fields', function (Blueprint $table) {
            $table->dropConstrainedForeignId('field_group_id');
            $table->string('field_group')->nullable()->after('sort_order');
        });

        Schema::dropIfExists('event_registration_field_groups');
    }
};
