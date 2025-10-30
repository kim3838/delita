<?php

namespace App\Policies;

use App\Models\User;

class EmployeeContactPolicy
{
    public function __construct(
        protected EmployeePolicy $employeePolicy
    ){}

    public function create(User $user): bool
    {
        return $this->employeePolicy->create($user);
    }
}
