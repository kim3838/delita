<?php

namespace App\Policies;

use App\Models\SalaryStatementModule;
use App\Models\User;

class SalaryStatementModulePolicy
{
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, SalaryStatementModule $salaryStatementModule): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, SalaryStatementModule $salaryStatementModule): bool
    {
        return $user->isSuperAdmin();
    }

    public function reOrder(User $user): bool
    {
        return $user->isSuperAdmin();
    }
}
