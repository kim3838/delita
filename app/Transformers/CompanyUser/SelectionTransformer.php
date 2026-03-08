<?php

namespace App\Transformers\CompanyUser;

use App\Concrete\TransformerAbstractConcrete;
use App\Models\Hydrations\CompanyUser;
use App\Models\User;

class SelectionTransformer extends TransformerAbstractConcrete
{
    public function transform(CompanyUser $model): array
    {
        $user = User::query()->find($model->user_id);

        $accountRoles = request()->account_id
            ? $this->collectionSummary($user->roles->where('account_id', request()->account_id)->values(), 'name', '')
            : null;

        $employeeFullName = $model->company_employee_full_name;
        $employeeNumberAndFullName = $model->is_employee ? ('(' . $model->company_employee_number . ') '  . $employeeFullName) : null;

        $label = implode(PHP_EOL, array_filter([
            $model->user_username,
            $employeeNumberAndFullName,
        ]));

        return [
            'value' => $model->user_id,
            'text' => $label
        ];
    }
}
