<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Venue: add phone, max_capacity, space_concept, type
        Schema::table('venues', function (Blueprint $table) {
            $table->string('phone', 50)->nullable()->after('address');
            $table->unsignedInteger('max_capacity')->nullable()->after('phone');
            $table->string('space_concept', 30)->nullable()->after('max_capacity');
            $table->string('type', 30)->nullable()->after('space_concept');
        });

        // Venue facilities table
        Schema::create('venue_facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Event: add type
        Schema::table('events', function (Blueprint $table) {
            $table->string('type', 50)->nullable()->after('description');
        });

        // EventSeries: add require_ticketing
        Schema::table('event_series', function (Blueprint $table) {
            $table->boolean('require_ticketing')->default(false)->after('sort_order');
        });

        // Budget: add event_series_id
        Schema::table('budgets', function (Blueprint $table) {
            $table->foreignId('event_series_id')->nullable()->after('event_id')
                ->constrained('event_series')->nullOnDelete();
        });

        // Expense: add event_series_id
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('event_series_id')->nullable()->after('event_id')
                ->constrained('event_series')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_series_id');
        });

        Schema::table('budgets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_series_id');
        });

        Schema::table('event_series', function (Blueprint $table) {
            $table->dropColumn('require_ticketing');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::dropIfExists('venue_facilities');

        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn(['phone', 'max_capacity', 'space_concept', 'type']);
        });
    }
};
