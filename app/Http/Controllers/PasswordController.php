<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function change(): View
    {
        return view('change-password');
    }

    public function update(ChangePasswordRequest $request): RedirectResponse
    {
        $this->userService->changePassword($request->user(), $request->password);

        return redirect()->route('change-password')->with('success', 'Password updated successfully.');
    }
}
