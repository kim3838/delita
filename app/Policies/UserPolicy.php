<?php

namespace App\Policies;

use App\Enums\CompanyUserAssignmentType;
use App\Models\CompanyUser;
use App\Models\User;

class UserPolicy
{
    public function create(User $user): bool
    {
        $isAdminInAnyCompany = (bool)CompanyUser::where('user_id', $user->id)
            ->where('assignment_type', CompanyUserAssignmentType::ADMIN->value)
            ->count();

        return $user->isSuperAdmin() || $isAdminInAnyCompany;
    }

    public function update(User $user, User $stagedUser): bool
    {
        $isAdminInAnyCompany = (bool)CompanyUser::where('user_id', $user->id)
            ->where('assignment_type', CompanyUserAssignmentType::ADMIN->value)
            ->count();

        $createdUser = $stagedUser->created_by == $user->id;

        return $user->isSuperAdmin() || ($isAdminInAnyCompany || $createdUser);
    }
}
