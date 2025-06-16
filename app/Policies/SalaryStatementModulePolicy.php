<?php

namespace App\Policies;

use App\Models\User;

class SalaryStatementModulePolicy
{
    public function reOrder(User $user): bool
    {
        return $user->isSuperAdmin();
    }
}
