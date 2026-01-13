<?php

namespace App\Policies;

use App\Models\ApprovalSetting;
use App\Models\User;

class ApprovalSettingPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, request()->input('company_id'))
            && $this->hasPermission($user, 'view-approval-setting');
    }

    public function update(User $user, ApprovalSetting $approvalSetting): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, request()->input('company_id'))
            && $this->hasPermission($user, 'manage-approval-setting');
    }
}
