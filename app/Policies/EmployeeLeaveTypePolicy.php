<?php

namespace App\Policies;

use App\Blueprint\RequestInterface;
use App\Models\EmployeeLeaveType;
use App\Models\User;

class EmployeeLeaveTypePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, app(RequestInterface::class)->companyId)
            && $this->hasPermission($user, 'view-employee-leave-type-assignment');
    }

    public function update(User $user, EmployeeLeaveType $employeeLeaveType): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, app(RequestInterface::class)->companyId)
            && $this->hasPermission($user, 'update-employee-leave-type-assignment');
    }

    public function delete(User $user, EmployeeLeaveType $employeeLeaveType): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, app(RequestInterface::class)->companyId)
            && $this->hasPermission($user, 'delete-employee-leave-type-assignment');
    }

    public function batchDelete(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, app(RequestInterface::class)->companyId)
            && $this->hasPermission($user, 'delete-employee-leave-type-assignment');
    }

    public function syncWithoutDetaching(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, app(RequestInterface::class)->companyId)
            && $this->hasPermission($user, 'create-employee-leave-type-assignment')
            && $this->hasPermission($user, 'update-employee-leave-type-assignment');
    }

    public function detachAssignedLeaveTypes(User $user): bool
    {
        if($user->isSuperAdmin()){
            return true;
        }

        return $this->userIsAdminInCompany($user, app(RequestInterface::class)->companyId)
            && $this->hasPermission($user, 'delete-employee-leave-type-assignment');
    }
}
