<?php

namespace App\Http\Requests\EmploymentProfile;

use App\Models\EmploymentProfile;
use Illuminate\Foundation\Http\FormRequest;

class DestroyEmploymentProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $employmentProfile = EmploymentProfile::query()->findOrFail($this->route('employmentProfileId'));

        return $this->user()->can('delete', $employmentProfile);
    }
}
