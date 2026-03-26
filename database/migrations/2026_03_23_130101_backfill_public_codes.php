<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('employees')
            ->whereNull('employee_code')
            ->orderBy('id')
            ->chunkById(100, function ($employees) {
                foreach ($employees as $employee) {
                    DB::table('employees')
                        ->where('id', $employee->id)
                        ->update([
                            'employee_code' => hash('sha256', Str::uuid()->toString() . microtime(true) . $employee->id),
                        ]);
                }
            });

        DB::table('vendors')
            ->whereNull('vendor_code')
            ->orderBy('id')
            ->chunkById(100, function ($vendors) {
                foreach ($vendors as $vendor) {
                    DB::table('vendors')
                        ->where('id', $vendor->id)
                        ->update([
                            'vendor_code' => hash('sha256', Str::uuid()->toString() . microtime(true) . $vendor->id),
                        ]);
                }
            });

        DB::table('sponsors')
            ->whereNull('sponsor_code')
            ->orderBy('id')
            ->chunkById(100, function ($sponsors) {
                foreach ($sponsors as $sponsor) {
                    DB::table('sponsors')
                        ->where('id', $sponsor->id)
                        ->update([
                            'sponsor_code' => hash('sha256', Str::uuid()->toString() . microtime(true) . $sponsor->id),
                        ]);
                }
            });
    }

    public function down(): void {}
};
