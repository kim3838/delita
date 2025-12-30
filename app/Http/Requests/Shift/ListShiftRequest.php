<?php

namespace App\Http\Requests\Shift;

use App\Models\Shift;
use Illuminate\Foundation\Http\FormRequest;

class ListShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Shift::class);
    }
}
