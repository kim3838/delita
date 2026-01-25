<?php

namespace App\Http\Requests\OvertimeRequest;

use App\Models\OvertimeRequest;
use Illuminate\Foundation\Http\FormRequest;

class ListOvertimeRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', OvertimeRequest::class);
    }
}
