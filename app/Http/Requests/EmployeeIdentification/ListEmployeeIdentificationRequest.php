<?php

namespace App\Http\Requests\EmployeeIdentification;

use App\Models\EmployeeIdentification;
use Illuminate\Foundation\Http\FormRequest;

class ListEmployeeIdentificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', EmployeeIdentification::class);
    }
}
