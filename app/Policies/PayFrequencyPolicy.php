<?php

namespace App\Policies;

use App\Enums\CompanyUserAssignmentType;
use App\Models\Company;
use App\Models\PayFrequency;
use App\Models\User;

class PayFrequencyPolicy
{
    public function update(User $user, PayFrequency $payFrequency): bool
    {
        $userCompanyAdminAssignment = Company::findOrFail(request()->input('company_id'))
                ->users
                ->findOrfail($user->id)
                ->pivot
                ->assignment_type == CompanyUserAssignmentType::ADMIN->value;

        return $user->isSuperAdmin() || $userCompanyAdminAssignment;
    }
}
