<?php

namespace App\Http\Requests\Employee;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;

class ListEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Employee::class);
    }
}
