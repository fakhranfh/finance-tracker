<?php

namespace App\Services;

use App\Mail\PendingEmailVerificationMail;
use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;

class UserService
{
    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function updateProfile(User $user, array $data): User
    {
        return $this->userRepository->update($user, $data);
    }

    public function updateProfilePhoto(User $user, UploadedFile $photo): void
    {
        $photoUrl = $this->userRepository->updateProfilePhoto($user, $photo);
        $user->update(['profile_photo_path' => $photoUrl]);
    }

    public function removeProfilePhoto(User $user): void
    {
        $this->userRepository->removeProfilePhoto($user);
    }

    public function changePassword(User $user, string $password): void
    {
        $this->userRepository->update($user, ['password' => $password]);
    }

    public function setPendingEmail(User $user, string $pendingEmail): void
    {
        $this->userRepository->setPendingEmail($user, $pendingEmail);
    }

    public function confirmPendingEmail(User $user): void
    {
        $this->userRepository->confirmPendingEmail($user);
    }

    public function sendPendingEmailVerification(User $user, string $verificationUrl): void
    {
        Mail::to($user->pending_email)->send(new PendingEmailVerificationMail($user, $verificationUrl));
    }

    public function getAll()
    {
        return $this->userRepository->getAll();
    }

    public function assignRole(User $user, string $roleName): User
    {
        return $this->userRepository->assignRole($user, $roleName);
    }
}
