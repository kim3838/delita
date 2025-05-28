<?php

namespace App\Http\Requests;

use App\Models\Compensation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCompensationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Compensation::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'name' => 'required|string|max:255',
            'assignable' => 'required|boolean',
            'type' => 'required|numeric',
            'company_formula_id' => 'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'assignable.required' => 'Assignable is required',
            'type.required' => 'Type is required',
            'company_formula_id.required' => 'Formula is required',
        ];
    }
}
