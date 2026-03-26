<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add series_number and registration_config to event_series
        Schema::table('event_series', function (Blueprint $table) {
            $table->string('series_number', 100)->nullable()->unique()->after('id');
            $table->json('registration_config')->nullable()->after('technical_instruction');
        });

        // 2. Add event_series_id to registration-related tables (replacing event_id)
        Schema::table('event_price_series', function (Blueprint $table) {
            $table->foreignId('event_series_id')->nullable()->after('event_id')
                ->constrained('event_series')->cascadeOnDelete();
        });

        Schema::table('event_categories', function (Blueprint $table) {
            $table->foreignId('event_series_id')->nullable()->after('event_id')
                ->constrained('event_series')->cascadeOnDelete();
        });

        Schema::table('event_registration_fields', function (Blueprint $table) {
            $table->foreignId('event_series_id')->nullable()->after('event_id')
                ->constrained('event_series')->cascadeOnDelete();
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->foreignId('event_series_id')->nullable()->after('event_id')
                ->constrained('event_series')->cascadeOnDelete();
        });

        // 3. Add event_series_id to vendor/staff assignment tables
        Schema::table('event_vendors', function (Blueprint $table) {
            $table->foreignId('event_series_id')->nullable()->after('event_id')
                ->constrained('event_series')->cascadeOnDelete();
        });

        Schema::table('event_staff', function (Blueprint $table) {
            $table->foreignId('event_series_id')->nullable()->after('event_id')
                ->constrained('event_series')->cascadeOnDelete();
        });

        // 4. Add event_series_id to purchase_orders
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('event_series_id')->nullable()->after('event_id')
                ->constrained('event_series')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_series_id');
        });

        Schema::table('event_staff', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_series_id');
        });

        Schema::table('event_vendors', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_series_id');
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_series_id');
        });

        Schema::table('event_registration_fields', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_series_id');
        });

        Schema::table('event_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_series_id');
        });

        Schema::table('event_price_series', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_series_id');
        });

        Schema::table('event_series', function (Blueprint $table) {
            $table->dropColumn(['series_number', 'registration_config']);
        });
    }
};
