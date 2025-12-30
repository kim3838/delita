<?php

namespace App\Http\Requests\Designation;

use App\Models\Designation;
use Illuminate\Foundation\Http\FormRequest;

class ListDesignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Designation::class);
    }
}
