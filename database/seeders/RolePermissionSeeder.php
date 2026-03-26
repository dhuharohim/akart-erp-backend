<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'events.view',
            'events.create',
            'events.update',
            'events.delete',
            'venues.view',
            'venues.create',
            'venues.update',
            'venues.delete',
            'vendors.view',
            'vendors.create',
            'vendors.update',
            'vendors.delete',
            'employees.view',
            'employees.create',
            'employees.update',
            'employees.delete',
            'teams.view',
            'teams.create',
            'teams.update',
            'teams.delete',
            'sponsors.view',
            'sponsors.create',
            'sponsors.update',
            'sponsors.delete',
            'assets.view',
            'assets.update',
            'purchase-orders.view',
            'purchase-orders.create',
            'purchase-orders.update',
            'purchase-orders.delete',
            'invoices.view',
            'invoices.create',
            'invoices.update',
            'invoices.delete',
            'payments.view',
            'payments.create',
            'payments.update',
            'payments.delete',
            'documents.view',
            'documents.create',
            'documents.update',
            'documents.delete',
            'coa.view',
            'coa.create',
            'coa.update',
            'coa.delete',
            'journals.view',
            'journals.create',
            'journals.update',
            'journals.delete',
            'rbac.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission]);
        }

        $admin = Role::query()->firstOrCreate(['name' => 'Admin']);
        $finance = Role::query()->firstOrCreate(['name' => 'Finance']);
        $operations = Role::query()->firstOrCreate(['name' => 'Operations']);
        $vendorCoordinator = Role::query()->firstOrCreate(['name' => 'Vendor Coordinator']);

        $admin->syncPermissions($permissions);
        $finance->syncPermissions([
            'events.view',
            'purchase-orders.view',
            'purchase-orders.create',
            'purchase-orders.update',
            'invoices.view',
            'invoices.create',
            'invoices.update',
            'payments.view',
            'payments.create',
            'payments.update',
            'documents.view',
            'documents.create',
            'coa.view',
            'coa.create',
            'coa.update',
            'coa.delete',
            'journals.view',
            'journals.create',
            'journals.update',
            'journals.delete',
        ]);
        $operations->syncPermissions([
            'events.view',
            'events.create',
            'events.update',
            'venues.view',
            'venues.create',
            'venues.update',
            'employees.view',
            'employees.create',
            'employees.update',
            'teams.view',
            'teams.create',
            'teams.update',
            'sponsors.view',
            'sponsors.create',
            'sponsors.update',
            'assets.view',
            'assets.update',
            'vendors.view',
            'documents.view',
            'documents.create',
        ]);
        $vendorCoordinator->syncPermissions([
            'vendors.view',
            'vendors.create',
            'vendors.update',
            'sponsors.view',
            'purchase-orders.view',
            'purchase-orders.create',
            'purchase-orders.update',
            'documents.view',
            'documents.create',
        ]);
    }
}
