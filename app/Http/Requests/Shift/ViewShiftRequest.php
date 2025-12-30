<?php

namespace App\Http\Requests\Shift;

use App\Models\Shift;
use Illuminate\Foundation\Http\FormRequest;

class ViewShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        $shift = Shift::query()->where('ulid', $this->route('ulid'))->firstOrFail();

        return $this->user()->can('view', $shift);
    }
}
