<?php

namespace App\Http\Requests\Holiday;

use App\Models\Holiday;
use Illuminate\Foundation\Http\FormRequest;

class ListHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Holiday::class);
    }
}
