<?php

namespace App\Http\Requests\EmployeePortal\OvertimeRequest;

use App\Models\OvertimeRequest;
use Illuminate\Foundation\Http\FormRequest;

class ViewOvertimeRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $overtimeRequest = OvertimeRequest::query()->where('number', $this->route('requestNumber'))->firstOrFail();

        return $overtimeRequest instanceof OvertimeRequest;
    }
}
