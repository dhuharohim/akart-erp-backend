<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'start_date')) {
                $table->dropIndex(['start_date']);
                $table->dropColumn('start_date');
            }
            if (Schema::hasColumn('events', 'end_date')) {
                $table->dropIndex(['end_date']);
                $table->dropColumn('end_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->date('start_date')->nullable()->index()->after('status');
            $table->date('end_date')->nullable()->index()->after('start_date');
        });
    }
};
