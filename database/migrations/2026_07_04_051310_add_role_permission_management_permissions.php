<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['view', 'create', 'update', 'delete'] as $action) {
            Permission::findOrCreate("{$action}-role");
        }

        $admin = Role::findOrCreate('admin');
        $admin->syncPermissions([
            ...$admin->permissions->pluck('name')->all(),
            'view-role', 'create-role', 'update-role', 'delete-role',
        ]);
    }

    public function down(): void
    {
        $admin = Role::findOrCreate('admin');
        $admin->syncPermissions(
            $admin->permissions->pluck('name')->reject(fn ($name) => str_ends_with($name, '-role'))->all()
        );

        foreach (['view', 'create', 'update', 'delete'] as $action) {
            Permission::findOrCreate("{$action}-role")->delete();
        }
    }
};
