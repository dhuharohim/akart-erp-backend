<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_series', function (Blueprint $table) {
            if (! Schema::hasColumn('event_series', 'checkin_passcode')) {
                $table->string('checkin_passcode', 6)->nullable()->after('registration_config');
            }
        });

        Schema::table('event_staff', function (Blueprint $table) {
            if (! Schema::hasColumn('event_staff', 'employee_number')) {
                $table->string('employee_number', 100)->nullable()->unique()->after('employee_id');
            }
            if (! Schema::hasColumn('event_staff', 'event_series_id')) {
                $table->foreignId('event_series_id')->nullable()->after('event_id')
                    ->constrained('event_series')->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('event_series', function (Blueprint $table) {
            $table->dropColumn('checkin_passcode');
        });
        Schema::table('event_staff', function (Blueprint $table) {
            $table->dropUnique(['employee_number']);
            $table->dropColumn(['employee_number']);
            if (Schema::hasColumn('event_staff', 'event_series_id')) {
                $table->dropForeign(['event_series_id']);
                $table->dropColumn('event_series_id');
            }
        });
    }
};
