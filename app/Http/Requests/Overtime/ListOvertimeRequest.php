<?php

namespace App\Http\Requests\Overtime;

use App\Models\Overtime;
use Illuminate\Foundation\Http\FormRequest;

class ListOvertimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Overtime::class);
    }
}
