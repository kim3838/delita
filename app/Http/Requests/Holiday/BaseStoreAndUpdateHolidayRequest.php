<?php

namespace App\Http\Requests\Holiday;

use Illuminate\Foundation\Http\FormRequest;

class BaseStoreAndUpdateHolidayRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric|integer',
            'date' => 'required|date_format:Y-m-d',
            'active' => 'required|boolean',
            'name' => 'required|string|max:255',
            'type' => 'required|numeric|integer',
            'recurring' => 'required|boolean',
            'effective_date' => 'required|date_format:Y-m-d',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'name.required' => 'Holiday name is required',
            'type.required' => 'Holiday type is required',
            'date.required' => 'Date is required',
            'recurring.required' => 'Recurring is required',
            'active.required' => 'Active is required',
            'effective_date.required' => 'Effective date is required',
        ];
    }
}
