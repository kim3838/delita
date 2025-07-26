<?php

namespace App\Policies;

use App\Enums\CompanyUserAssignmentType;
use App\Models\Company;
use App\Models\Compensation;
use App\Models\User;

class CompensationPolicy
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

    public function update(User $user, Compensation $compensation): bool
    {
        $userCompanyAdminAssignment = $compensation
                ->companyFormula
                ->company
                ->users
                ->findOrfail($user->id)
                ->pivot
                ->assignment_type == CompanyUserAssignmentType::ADMIN->value;

        return $user->isSuperAdmin() || $userCompanyAdminAssignment;
    }

    public function delete(User $user, Compensation $compensation): bool
    {
        return $user->isSuperAdmin();
    }
}
