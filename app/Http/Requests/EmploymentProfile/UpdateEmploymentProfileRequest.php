<?php

namespace App\Http\Requests\EmploymentProfile;

use App\Models\EmploymentProfile;

class UpdateEmploymentProfileRequest extends BaseStoreAndUpdateEmploymentProfileRequest
{
    public function authorize(): bool
    {
        $employmentProfile = EmploymentProfile::query()->findOrFail($this->route('employmentProfileId'));

        return $this->user()->can('update', $employmentProfile);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'employee_id' => 'required|numeric|integer',
        ]);
    }
}
