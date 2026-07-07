<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\ResetUserPasswordRequest;
use App\Http\Requests\User\UpdateUserRoleRequest;
use App\Models\User;
use App\Services\RoleService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private UserService $userService, private RoleService $roleService) {}

    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $users = $this->userService->getAll();
        $roles = $this->roleService->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')->withErrors(['role' => 'You cannot change your own role.']);
        }

        $this->userService->assignRole($user, $request->validated('role'));

        return redirect()->route('users.index')->with('success', 'User role updated successfully.');
    }

    public function resetPassword(ResetUserPasswordRequest $request, User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')->withErrors(['password' => 'Use the change password page to update your own password.']);
        }

        $this->userService->changePassword($user, $request->validated('password'));

        return redirect()->route('users.index')->with('success', "Password for {$user->name} has been reset.");
    }
}
