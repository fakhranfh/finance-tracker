<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed the application's roles and permissions.
     */
    public function run(): void
    {
        $entities = ['wallet', 'category', 'transaction', 'transfer', 'user'];
        $actions = ['view', 'create', 'update', 'delete'];

        foreach ($entities as $entity) {
            foreach ($actions as $action) {
                Permission::findOrCreate("{$action}-{$entity}");
            }
        }

        Role::findOrCreate('admin')->syncPermissions([
            // Full control over user accounts.
            'view-user', 'create-user', 'update-user', 'delete-user',
            // View-only over other users' financial data (support/audit), never mutate it.
            'view-wallet', 'view-category', 'view-transaction', 'view-transfer',
            // Full control over their own financial data, same as any user.
            'create-wallet', 'update-wallet', 'delete-wallet',
            'create-category', 'update-category', 'delete-category',
            'create-transaction', 'update-transaction', 'delete-transaction',
            'create-transfer', 'update-transfer', 'delete-transfer',
        ]);

        Role::findOrCreate('user')->syncPermissions([
            'view-wallet', 'create-wallet', 'update-wallet', 'delete-wallet',
            'view-category', 'create-category', 'update-category', 'delete-category',
            'view-transaction', 'create-transaction', 'update-transaction', 'delete-transaction',
            'view-transfer', 'create-transfer', 'update-transfer', 'delete-transfer',
        ]);
    }
}
