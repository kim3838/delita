<?php

namespace App\Http\Requests\Designation;

use App\Models\Designation;
use Illuminate\Foundation\Http\FormRequest;

class DestroyDesignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $designation = Designation::findOrfail($this->route('designationId'));

        return $this->user()->can('delete', $designation);
    }
}
