<?php

namespace App\Http\Requests\EmploymentProfile;

use App\Models\EmploymentProfile;
use Illuminate\Foundation\Http\FormRequest;

class ListEmploymentProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', EmploymentProfile::class);
    }
}
