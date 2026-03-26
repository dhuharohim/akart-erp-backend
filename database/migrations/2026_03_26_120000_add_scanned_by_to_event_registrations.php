<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            if (! Schema::hasColumn('event_registrations', 'scanned_by_type')) {
                $table->string('scanned_by_type', 30)->nullable()->after('checked_in_at');
                $table->unsignedBigInteger('scanned_by_id')->nullable()->after('scanned_by_type');
                $table->index(['scanned_by_type', 'scanned_by_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropIndex(['scanned_by_type', 'scanned_by_id']);
            $table->dropColumn(['scanned_by_type', 'scanned_by_id']);
        });
    }
};
