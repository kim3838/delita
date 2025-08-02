<?php

namespace App\Http\Requests\Compensation;

use App\Http\Requests\BaseEmployeePayrollComponentRequest;
use App\Models\Compensation;
use Illuminate\Validation\Rule;

class StoreCompensationRequest extends BaseEmployeePayrollComponentRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Compensation::class);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('compensations')->where(function ($query) {
                    return $query->where('company_id', $this->input('company_id'));
                })
            ],

        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'code.required' => 'Code is required',
            'code.unique' => 'Code has already been taken.',
        ]);
    }
}
