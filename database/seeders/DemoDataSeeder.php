<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorService;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::factory()->create([
            'name' => 'Akart Events',
            'code' => 'AKART',
        ]);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'name' => 'Admin User',
            'email' => 'admin@akart.test',
            'password' => 'password',
        ]);

        $admin->assignRole('Admin');
    }
}
