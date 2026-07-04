<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    private const PROTECTED_ROLES = ['admin', 'user'];

    public function viewAny(User $user): bool
    {
        return $user->can('view-role');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('view-role');
    }

    public function create(User $user): bool
    {
        return $user->can('create-role');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('update-role');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can('delete-role') && ! in_array($role->name, self::PROTECTED_ROLES, true);
    }
}
