<?php

namespace App\Transformers\AssociatedCompany;

use App\Facades\Fractal;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Hydrations\AssociatedCompany;
use App\Transformers\Account\BasicTransformer as AccountBasicTransformer;
use App\Transformers\Employee\ItemTransformer as EmployeeItemTransformer;
use League\Fractal\TransformerAbstract;

class SelectionTransformer extends TransformerAbstract
{
    public function transform(AssociatedCompany $model): array
    {
        $company = Company::query()->find($model->company_id);
        $account = Fractal::item($company->account, AccountBasicTransformer::class);
        $employee = Employee::query()
            ->where('user_id', $model->user_id)
            ->where('company_id', $model->company_id)
            ->first();

        $isEmployeeAtCompany = (bool)$employee;

        $employee = $employee ? Fractal::item($employee, EmployeeItemTransformer::class): $employee;

        return [
            'value' => $model->company_id,
            'text' => $model->company_short_name,
            'payload' => [
                'ulid' => $model->company_ulid,
                'currency' => $model->company_currency,
                'timezone' => $model->company_timezone,
                'assignment_type' => $model->assignment_type?->toArray(),
                'is_employee' => $isEmployeeAtCompany,
                'employee' => $employee,
                'account' => $account,
            ]
        ];
    }
}
