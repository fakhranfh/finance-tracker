<?php

namespace App\Repositories\Role;

use Spatie\Permission\Models\Role;

interface RoleRepositoryInterface
{
    public function get();

    public function find($id): Role;

    public function create(array $data): Role;

    public function update($id, array $data): Role;

    public function delete($id): void;

    public function syncPermissions($id, array $permissions): Role;
}
