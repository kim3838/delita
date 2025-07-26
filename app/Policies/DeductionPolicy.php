<?php

namespace App\Policies;

use App\Enums\CompanyUserAssignmentType;
use App\Models\Company;
use App\Models\Deduction;
use App\Models\User;

class DeductionPolicy
{
    public function create(User $user): bool
    {
        $userCompanyAdminAssignment = Company::findOrFail(request()->input('company_id'))
                ->users
                ->findOrfail($user->id)
                ->pivot
                ->assignment_type == CompanyUserAssignmentType::ADMIN->value;

        return $user->isSuperAdmin() || $userCompanyAdminAssignment;
    }

    public function update(User $user, Deduction $deduction): bool
    {
        $userCompanyAdminAssignment = $deduction
                ->companyFormula
                ->company
                ->users
                ->findOrfail($user->id)
                ->pivot
                ->assignment_type == CompanyUserAssignmentType::ADMIN->value;

        return $user->isSuperAdmin() || $userCompanyAdminAssignment;
    }

    public function delete(User $user, Deduction $deduction): bool
    {
        return $user->isSuperAdmin();
    }
}
