<?php

namespace App\Services;

use App\Repositories\Role\RoleRepositoryInterface;

class RoleService
{
    public function __construct(private RoleRepositoryInterface $roleRepository) {}

    public function get()
    {
        return $this->roleRepository->get();
    }

    public function find($id)
    {
        return $this->roleRepository->find($id);
    }

    public function create(array $data)
    {
        return $this->roleRepository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->roleRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->roleRepository->delete($id);
    }

    public function syncPermissions($id, array $permissions)
    {
        return $this->roleRepository->syncPermissions($id, $permissions);
    }
}
