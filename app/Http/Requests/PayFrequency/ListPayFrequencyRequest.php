<?php

namespace App\Http\Requests\PayFrequency;

use App\Models\PayFrequency;
use Illuminate\Foundation\Http\FormRequest;

class ListPayFrequencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', PayFrequency::class);
    }
}
