<?php

namespace App\Http\Requests\Leave;

use App\Models\Leave;
use Illuminate\Foundation\Http\FormRequest;

class ListLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Leave::class);
    }
}
