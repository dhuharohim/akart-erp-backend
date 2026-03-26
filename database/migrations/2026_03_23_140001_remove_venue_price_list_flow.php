<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_series_venue', function (Blueprint $table) {
            if (Schema::hasColumn('event_series_venue', 'venue_price_id')) {
                $table->dropConstrainedForeignId('venue_price_id');
            }
            if (Schema::hasColumn('event_series_venue', 'period_days')) {
                $table->dropColumn('period_days');
            }
            if (!Schema::hasColumn('event_series_venue', 'description')) {
                $table->text('description')->nullable()->after('amount');
            }
        });

        Schema::dropIfExists('venue_prices');
    }

    public function down(): void
    {
        Schema::create('venue_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained('venues')->cascadeOnDelete();
            $table->string('name');
            $table->enum('day_type', ['weekday', 'weekend', 'custom'])->default('custom');
            $table->unsignedInteger('period_days')->default(1);
            $table->decimal('amount', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('event_series_venue', function (Blueprint $table) {
            if (Schema::hasColumn('event_series_venue', 'description')) {
                $table->dropColumn('description');
            }
            if (!Schema::hasColumn('event_series_venue', 'period_days')) {
                $table->unsignedInteger('period_days')->nullable()->after('end_datetime');
            }
            if (!Schema::hasColumn('event_series_venue', 'venue_price_id')) {
                $table->foreignId('venue_price_id')->nullable()->after('venue_id')
                    ->constrained('venue_prices')->nullOnDelete();
            }
        });
    }
};
