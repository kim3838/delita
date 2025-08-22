<?php

namespace App\Http\Requests\Formula;

use App\Models\Formula;
use Illuminate\Validation\Rule;

class StoreFormulaRequest extends BaseFormulaRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Formula::class);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('formulas')
            ],
        ]);
    }
}
