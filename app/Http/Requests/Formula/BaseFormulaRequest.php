<?php

namespace App\Http\Requests\Formula;

use Illuminate\Foundation\Http\FormRequest;

class BaseFormulaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'formulable_type' => 'required|numeric',
            'component_type' => 'nullable|numeric',
            'aggregation' => 'required|boolean',
            'default_settings' => 'nullable|array',
        ];
    }

}
