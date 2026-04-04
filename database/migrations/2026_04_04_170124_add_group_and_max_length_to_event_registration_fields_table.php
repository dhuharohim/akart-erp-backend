<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_registration_fields', function (Blueprint $table) {
            $table->string('field_group')->nullable()->after('sort_order');
            $table->unsignedInteger('max_length')->nullable()->after('field_group');
        });
    }

    public function down(): void
    {
        Schema::table('event_registration_fields', function (Blueprint $table) {
            $table->dropColumn(['field_group', 'max_length']);
        });
    }
};
