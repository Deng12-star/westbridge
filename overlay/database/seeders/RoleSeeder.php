<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permissions are named module.action and enforced in Policies — hiding a
 * button in Blade is presentation, the Policy is the control.
 *
 * Per decision D6 only four roles are seeded. The remaining three
 * (Administrator, Accountant, Technician) are created from the admin UI on the
 * day someone is hired into them; their permission sets are listed below for
 * reference so they can be assembled consistently.
 */
class RoleSeeder extends Seeder
{
    /** @var array<string, string[]> */
    private const PERMISSIONS = [
        'dashboard' => ['view'],
        'products' => ['view', 'create', 'update', 'delete'],
        'categories' => ['view', 'create', 'update', 'delete'],
        'inventory' => ['view', 'adjust', 'receive'],
        'orders' => ['view', 'create', 'update_status', 'cancel'],
        'payments' => ['view', 'record', 'refund'],
        'quotations' => ['view', 'create', 'update', 'send', 'convert'],
        'invoices' => ['view', 'create', 'update', 'void'],
        'receipts' => ['view', 'issue', 'reprint'],
        'leads' => ['view', 'create', 'update', 'assign', 'delete'],
        'customers' => ['view', 'create', 'update'],
        'content' => ['view', 'create', 'update', 'publish', 'delete'],
        'projects' => ['view', 'create', 'update', 'delete'],
        'settings' => ['view', 'manage'],
        'users' => ['view', 'create', 'update', 'delete'],
        'activity' => ['view'],
    ];

    /** @var array<string, string[]|string> */
    private const ROLES = [
        'Super Admin' => '*',
        'Sales' => [
            'dashboard.view',
            'products.view', 'inventory.view',
            'orders.view', 'orders.create', 'orders.update_status',
            'payments.view',
            'quotations.view', 'quotations.create', 'quotations.update', 'quotations.send', 'quotations.convert',
            'invoices.view',
            'leads.view', 'leads.create', 'leads.update', 'leads.assign',
            'customers.view', 'customers.create', 'customers.update',
        ],
        'Inventory Manager' => [
            'dashboard.view',
            'products.view', 'products.create', 'products.update',
            'categories.view', 'categories.create', 'categories.update',
            'inventory.view', 'inventory.adjust', 'inventory.receive',
            'orders.view',
        ],
        'Content Manager' => [
            'dashboard.view',
            'content.view', 'content.create', 'content.update', 'content.publish',
            'projects.view', 'projects.create', 'projects.update',
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $all = [];

        foreach (self::PERMISSIONS as $module => $actions) {
            foreach ($actions as $action) {
                $name = "{$module}.{$action}";
                Permission::findOrCreate($name, 'web');
                $all[] = $name;
            }
        }

        foreach (self::ROLES as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissions === '*' ? $all : $permissions);
        }
    }
}
