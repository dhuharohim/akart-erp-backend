<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add description to events
        Schema::table('events', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
        });

        // 2. Add status and description to event_series
        Schema::table('event_series', function (Blueprint $table) {
            $table->string('status', 20)->default('pra')->after('name');
            $table->text('description')->nullable()->after('status');
        });

        // 3. Create event_series_venue pivot table (many-to-many with datetime)
        Schema::create('event_series_venue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_series_id')->constrained('event_series')->cascadeOnDelete();
            $table->foreignId('venue_id')->constrained('venues')->cascadeOnDelete();
            $table->dateTime('start_datetime')->nullable();
            $table->dateTime('end_datetime')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['event_series_id', 'venue_id', 'start_datetime'], 'esv_unique');
        });

        // 4. Migrate existing venue_id data to pivot table
        $rows = DB::table('event_series')->whereNotNull('venue_id')->get();
        foreach ($rows as $row) {
            DB::table('event_series_venue')->insert([
                'event_series_id' => $row->id,
                'venue_id' => $row->venue_id,
                'start_datetime' => $row->start_date,
                'end_datetime' => $row->end_date,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('event_series_venue');

        Schema::table('event_series', function (Blueprint $table) {
            $table->dropColumn(['status', 'description']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
