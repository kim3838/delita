<?php

namespace App\Policies;

use App\Enums\CompanyUserAssignmentType;
use App\Models\Company;
use App\Models\IncomeTax;
use App\Models\User;

class IncomeTaxPolicy
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

    public function update(User $user, IncomeTax $incomeTax): bool
    {
        $userCompanyAdminAssignment = $incomeTax
                ->companyFormula
                ->company
                ->users
                ->findOrfail($user->id)
                ->pivot
                ->assignment_type == CompanyUserAssignmentType::ADMIN->value;

        return $user->isSuperAdmin() || $userCompanyAdminAssignment;
    }

    public function delete(User $user, IncomeTax $incomeTax): bool
    {
        return $user->isSuperAdmin();
    }
}
