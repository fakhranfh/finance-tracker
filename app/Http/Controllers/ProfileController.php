<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UpdateProfileRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Laravel\Fortify\Features;

class ProfileController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function edit(): View
    {
        return view('edit-profile');
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $data = $request->validated();

        if ($request->hasFile('profile_photo')) {
            $this->userService->updateProfilePhoto($user, $request->file('profile_photo'));
        }

        if (isset($data['remove_photo']) && $data['remove_photo']) {
            $this->userService->removeProfilePhoto($user);
        }

        unset($data['profile_photo'], $data['remove_photo']);

        if (Features::enabled(Features::emailVerification())
            && isset($data['email']) && $data['email'] !== $user->email) {
            $pendingEmail = $data['email'];
            unset($data['email']);

            $this->userService->setPendingEmail($user, $pendingEmail);
            $this->userService->updateProfile($user, $data);

            $verificationUrl = URL::temporarySignedRoute(
                'profile.verify-email-change',
                now()->addMinutes(60),
                ['user' => $user->id],
            );

            $this->userService->sendPendingEmailVerification($user, $verificationUrl);

            return redirect()->route('edit-profile')->with('pending_email_sent', $pendingEmail);
        }

        $this->userService->updateProfile($user, $data);

        return redirect()->route('edit-profile')->with('success', 'Profile updated successfully.');
    }

    public function verifyEmailChange(Request $request): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired verification link.');
        }

        /** @var User $user */
        $user = auth()->user();

        if (! $user->pending_email) {
            return redirect()->route('edit-profile')->with('success', 'No pending email change found.');
        }

        $this->userService->confirmPendingEmail($user);

        return redirect()->route('edit-profile')->with('success', 'Email address updated successfully.');
    }
}
