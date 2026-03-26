<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_series', function (Blueprint $table) {
            $table->string('public_id', 36)->nullable()->unique();
        });

        // Back-fill existing rows with UUIDs
        foreach (DB::table('event_series')->whereNull('public_id')->cursor() as $row) {
            DB::table('event_series')->where('id', $row->id)->update(['public_id' => Str::uuid()->toString()]);
        }

        Schema::table('event_series', function (Blueprint $table) {
            $table->string('public_id', 36)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('event_series', function (Blueprint $table) {
            $table->dropColumn('public_id');
        });
    }
};
