<?php

namespace App\Http\Requests\TaxCalculator;

use Illuminate\Foundation\Http\FormRequest;

class TaxCalculatorRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'monthly_salary' => 'required|numeric|min:0|max:999999999999',
        ];
    }
}
