<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Attendance $attendance): bool
    {
        return true;
    }
}
