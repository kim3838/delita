<?php

namespace App\Http\Requests\Compensation;

use App\Http\Requests\BaseEmployeePayrollComponentRequest;
use App\Models\Compensation;
use Illuminate\Validation\Rule;

class UpdateCompensationRequest extends BaseEmployeePayrollComponentRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $compensation = Compensation::query()->findOrfail($this->route('compensationId'));

        return $this->user()->can('update', $compensation);
    }

    public function rules(): array
    {
        return array_merge([
            'code' => [
                'required',
                'string',
                'regex:/^\S+$/',
                'max:255',
                Rule::unique('compensations')->where(function ($query) {
                    return $query->where('company_id', $this->input('company_id'))
                        ->whereNot('id', $this->route('compensationId'));
                })
            ],
        ], parent::rules());
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'code.required' => 'Code is required',
            'code.regex' => 'Code must not contain spaces',
            'code.unique' => 'Code has already been taken',
        ]);
    }
}
