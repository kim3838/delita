<?php

namespace App\Transformers\Department;

use App\Enums\DepartmentEmployeeAssignmentType;
use App\Facades\Fractal;
use App\Models\Department;
use App\Transformers\Employee\ItemTransformer as EmployeeItemTransformer;
use League\Fractal\TransformerAbstract;

class ListTransformer extends TransformerAbstract
{
    public function transform(Department $model): array
    {
        $head = $model->employees()
            ->where('department_assignment_type', DepartmentEmployeeAssignmentType::HEAD->value)
            ->first();

        $head = $head ? Fractal::item($head, EmployeeItemTransformer::class) : null;

        return [
            'id' => $model->id,
            'company_id' => $model->company_id,
            'parent_id' => $model->parent_id,
            'name' => $model->name,
            'head' => $head,
            'details' => [
                'sub_departments' => Fractal::collection($model->subDepartments->sortBy('name')->values(), ListTransformer::class)['data']
            ]
        ];
    }
}
