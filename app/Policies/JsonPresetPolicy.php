<?php

namespace App\Policies;

use App\Models\JsonPreset;
use App\Models\User;

class JsonPresetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, JsonPreset $jsonPreset): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, JsonPreset $jsonPreset): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, JsonPreset $jsonPreset): bool
    {
        return $user->isSuperAdmin();
    }
}
