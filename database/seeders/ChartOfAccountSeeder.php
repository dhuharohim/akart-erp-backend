<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['code' => '1100', 'name' => 'Cash On Hand', 'type' => 'cash_bank'],
            ['code' => '1110', 'name' => 'Bank Account', 'type' => 'cash_bank'],
            ['code' => '1200', 'name' => 'Accounts Receivable', 'type' => 'asset'],
            ['code' => '1500', 'name' => 'Inventory Assets', 'type' => 'asset'],
            ['code' => '2100', 'name' => 'Accounts Payable', 'type' => 'liability'],
            ['code' => '2200', 'name' => 'Accrued Liabilities', 'type' => 'liability'],
            ['code' => '3100', 'name' => 'Owner Equity', 'type' => 'equity'],
            ['code' => '4100', 'name' => 'Operating Revenue', 'type' => 'revenue'],
            ['code' => '4200', 'name' => 'Other Revenue', 'type' => 'other_revenue'],
            ['code' => '5100', 'name' => 'Operating Expense', 'type' => 'expense'],
            ['code' => '5200', 'name' => 'Other Expense', 'type' => 'other_expense'],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::query()->updateOrCreate(
                ['code' => $account['code']],
                [
                    'name' => $account['name'],
                    'type' => $account['type'],
                    'is_active' => true,
                ]
            );
        }
    }
}
