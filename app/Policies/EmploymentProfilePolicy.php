<?php

namespace App\Policies;

use App\Models\EmploymentProfile;
use App\Models\User;

class EmploymentProfilePolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, EmploymentProfile $employmentProfile): bool
    {
        return true;
    }
}
