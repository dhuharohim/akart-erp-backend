<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_details', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_details', 'unit')) {
                $table->string('unit', 50)->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoice_details', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_details', 'unit')) {
                $table->dropColumn('unit');
            }
        });
    }
};
