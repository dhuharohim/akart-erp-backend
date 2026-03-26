<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('sponsors')
            ->orderBy('id')
            ->chunkById(100, function ($sponsors) {
                foreach ($sponsors as $sponsor) {
                    $code = (string) ($sponsor->sponsor_code ?? '');
                    $isSha256 = preg_match('/^[a-f0-9]{64}$/', $code) === 1;
                    if ($isSha256) {
                        continue;
                    }

                    DB::table('sponsors')
                        ->where('id', $sponsor->id)
                        ->update([
                            'sponsor_code' => hash('sha256', Str::uuid()->toString().microtime(true).$sponsor->id),
                        ]);
                }
            });
    }

    public function down(): void
    {
    }
};
