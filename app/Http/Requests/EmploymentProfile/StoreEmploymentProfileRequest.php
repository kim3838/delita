<?php

namespace App\Http\Requests\EmploymentProfile;

use App\Models\EmploymentProfile;

class StoreEmploymentProfileRequest extends BaseStoreAndUpdateEmploymentProfileRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', EmploymentProfile::class);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'employee_id' => 'sometimes|required|numeric|integer',
        ]);
    }
}
