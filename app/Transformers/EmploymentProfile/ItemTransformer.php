<?php

namespace App\Transformers\EmploymentProfile;

use App\Facades\TimeZoneConverterFacade;
use App\Models\EmploymentProfile;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(EmploymentProfile $model): array
    {
        return [
            'id' => $model->id,
            'employee_id' => $model->employee_id,
            'status' => $model->status->toArray(),
            'employment_type' => $model->employment_type->toArray(),
            'start_date' => TimeZoneConverterFacade::globalToLocal($model->start_date),
            'end_of_service_type' => $model->end_of_service_type?->toArray(),
            'end_date' => TimeZoneConverterFacade::globalToLocal($model->end_date),
        ];
    }
}
