<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(private RoleService $roleService) {}

    public function index(): View
    {
        $this->authorize('viewAny', Role::class);

        $roles = $this->roleService->get();
        $permissionsByEntity = Permission::all()
            ->groupBy(fn (Permission $permission) => explode('-', $permission->name, 2)[1] ?? 'other');

        return view('roles.index', compact('roles', 'permissionsByEntity'));
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $role = $this->roleService->create($data);
        $this->roleService->syncPermissions($role->id, $data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();
        $this->roleService->update($role->id, $data);
        $this->roleService->syncPermissions($role->id, $data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        $this->roleService->delete($role->id);

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}
