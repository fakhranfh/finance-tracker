<?php

namespace App\Repositories\Role;

use Spatie\Permission\Models\Role;

class RoleRepository implements RoleRepositoryInterface
{
    public function get()
    {
        return Role::with('permissions')->get();
    }

    public function find($id): Role
    {
        return Role::with('permissions')->findOrFail($id);
    }

    public function create(array $data): Role
    {
        return Role::create(['name' => $data['name'], 'guard_name' => 'web']);
    }

    public function update($id, array $data): Role
    {
        $role = Role::findOrFail($id);
        $role->update(['name' => $data['name']]);

        return $role;
    }

    public function delete($id): void
    {
        Role::findOrFail($id)->delete();
    }

    public function syncPermissions($id, array $permissions): Role
    {
        $role = Role::findOrFail($id);
        $role->syncPermissions($permissions);

        return $role;
    }
}
