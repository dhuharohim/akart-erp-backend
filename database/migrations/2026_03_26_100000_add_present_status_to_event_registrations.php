<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            if (! Schema::hasColumn('event_registrations', 'present_status')) {
                $table->string('present_status', 30)->default('absent')->after('payment_status');
                $table->dateTime('checked_in_at')->nullable()->after('present_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropColumn(['present_status', 'checked_in_at']);
        });
    }
};
