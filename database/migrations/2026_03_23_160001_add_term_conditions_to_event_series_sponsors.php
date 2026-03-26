<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_series_sponsors', function (Blueprint $table) {
            if (!Schema::hasColumn('event_series_sponsors', 'term_conditions')) {
                $table->text('term_conditions')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('event_series_sponsors', function (Blueprint $table) {
            if (Schema::hasColumn('event_series_sponsors', 'term_conditions')) {
                $table->dropColumn('term_conditions');
            }
        });
    }
};
