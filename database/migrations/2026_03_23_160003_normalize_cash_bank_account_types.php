<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('chart_of_accounts')
            ->whereIn('code', ['1100', '1110'])
            ->update(['type' => 'cash_bank']);

        DB::table('chart_of_accounts')
            ->where('type', 'asset')
            ->where(function ($query) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%cash%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%bank%']);
            })
            ->update(['type' => 'cash_bank']);
    }

    public function down(): void
    {
    }
};
