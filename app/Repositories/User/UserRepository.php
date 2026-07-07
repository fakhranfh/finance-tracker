<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Services\ImageExifStripper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(private ImageExifStripper $exifStripper) {}

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user;
    }

    public function updateProfilePhoto(User $user, UploadedFile $photo): string
    {
        $this->removeProfilePhotoFile($user);

        $path = 'profile-photos/'.$photo->hashName();
        $contents = $this->exifStripper->strip(file_get_contents($photo->getRealPath()), $photo->getMimeType());
        Storage::disk('public')->put($path, $contents);

        return Storage::url($path);
    }

    public function removeProfilePhoto(User $user): void
    {
        $this->removeProfilePhotoFile($user);
        $user->update(['profile_photo_path' => null]);
    }

    public function setPendingEmail(User $user, string $pendingEmail): void
    {
        $user->update(['pending_email' => $pendingEmail]);
    }

    public function confirmPendingEmail(User $user): void
    {
        $user->update([
            'email' => $user->pending_email,
            'pending_email' => null,
            'email_verified_at' => now(),
        ]);
    }

    public function getAll()
    {
        return User::with('roles')->get();
    }

    public function assignRole(User $user, string $roleName): User
    {
        $user->syncRoles([$roleName]);

        return $user;
    }

    private function removeProfilePhotoFile(User $user): void
    {
        if ($user->profile_photo_path) {
            $path = str_replace('/storage/', '', $user->profile_photo_path);
            Storage::disk('public')->delete($path);
        }
    }
}
