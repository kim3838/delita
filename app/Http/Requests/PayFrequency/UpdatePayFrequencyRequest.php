<?php

namespace App\Http\Requests\PayFrequency;

use App\Models\PayFrequency;
use Illuminate\Validation\Rule;

class UpdatePayFrequencyRequest extends BasePayFrequencyRequest
{
    public function authorize(): bool
    {
        $payFrequency = PayFrequency::query()->findOrfail($this->route('payFrequencyId'));

        return $this->user()->can('update', $payFrequency);
    }

    public function rules(): array
    {
        return array_merge([
            'code' => [
                'required',
                'string',
                'regex:/^\S+$/',
                'max:255',
                Rule::unique('pay_frequencies')->where(function ($query) {
                    return $query->where('company_id', $this->input('company_id'))
                        ->whereNot('id', $this->route('payFrequencyId'));
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
