<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('vendor_code')->unique()->nullable()->after('company_id');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->string('employee_code')->unique()->nullable()->after('company_id');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('vendor_code');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('employee_code');
        });
    }
};
