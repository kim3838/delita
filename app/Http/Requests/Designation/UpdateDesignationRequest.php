<?php

namespace App\Http\Requests\Designation;

use App\Models\Designation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDesignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $designation = Designation::query()->findOrfail($this->route('designationId'));

        return $this->user()->can('update', $designation);
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'name' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
        ];
    }
}
