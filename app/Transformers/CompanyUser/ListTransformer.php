<?php

namespace App\Transformers\CompanyUser;

use App\Concrete\TransformerAbstractConcrete;
use App\Models\Hydrations\CompanyUser;
use App\Models\User;

class ListTransformer extends TransformerAbstractConcrete
{
    public function transform(CompanyUser $model): array
    {
        $user = User::query()->find($model->user_id);

        $accountRoles = request()->account_id
            ? $this->collectionSummary($user->roles->where('account_id', request()->account_id)->values(), 'name', '')
            : null;

        return [
            'company_id' => $model->company_id,
            'company_name' => $model->company_name,
            'company_assignment_type' => $model->company_assignment_type?->toArray(),
            'is_employee' => $model->is_employee,
            'company_employee_number' => $model->company_employee_number,
            'company_employee_full_name' => $model->company_employee_full_name,

            'id' => $model->user_id,
            'ulid' => $model->user_ulid,
            'username' => $model->user_username,
            'email' => $model->user_email,
            'status' => $model->user_status?->toArray(),
            'email_verified_at' => $model->user_email_verified_at,
            'timezone' => $model->user_timezone,

            'account_roles_summary' => $accountRoles,
        ];
    }
}
