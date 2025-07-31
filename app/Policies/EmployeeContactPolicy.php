<?php

namespace App\Policies;

use App\Models\EmployeeContact;
use App\Models\User;

class EmployeeContactPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, EmployeeContact $employeeContact): bool
    {
        return true;
    }
}
