<?php

namespace App\Http\Requests\Formula;

use App\Models\Formula;
use Illuminate\Validation\Rule;

class UpdateFormulaRequest extends BaseFormulaRequest
{
    public function authorize(): bool
    {
        $formula = Formula::query()->findOrfail($this->route('formulaId'));

        return $this->user()->can('update', $formula);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('formulas')->ignore($this->route('formulaId'))
            ],
        ]);
    }
}
