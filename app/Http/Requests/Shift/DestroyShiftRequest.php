<?php

namespace App\Http\Requests\Shift;

use App\Models\Shift;
use Illuminate\Foundation\Http\FormRequest;

class DestroyShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        $shift = Shift::query()->findOrFail($this->route('shiftId'));

        return $this->user()->can('delete', $shift);
    }
}
