<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_category_prices', function (Blueprint $table) {
            $table->unsignedInteger('quota')->default(0)->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('event_category_prices', function (Blueprint $table) {
            $table->dropColumn('quota');
        });
    }
};
