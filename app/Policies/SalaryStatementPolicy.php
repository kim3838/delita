<?php

namespace App\Policies;

use App\Models\SalaryStatement;
use App\Models\User;

class SalaryStatementPolicy extends BasePolicy
{
    public function view(User $user, SalaryStatement $salaryStatement): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, request()->input('company_id'))
            && $this->hasPermission($user, 'view-payroll');
    }

    public function delete(User $user, SalaryStatement $salaryStatement): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, request()->input('company_id'))
            && $this->hasPermission($user, 'delete-salary-statement');
    }

    public function batchDelete(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, request()->input('company_id'))
            && $this->hasPermission($user, 'delete-salary-statement');
    }
}
