<?php

namespace App\Http\Requests\EmployeePortal\UserFiledRequest;

use Illuminate\Foundation\Http\FormRequest;

class BatchDestroyUserFiledRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'requestables' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'requestables.required' => 'Requestable is required',
            'requestables.array' => 'ARequestable must be an array',
        ];
    }
}
