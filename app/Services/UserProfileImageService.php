<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserProfileImageService
{
    public function store(UploadedFile $image, ?User $user = null): string
    {
        if ($user?->profile_photo_path) {
            $this->delete($user->profile_photo_path);
        }

        return $image->store('users', 'public');
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
