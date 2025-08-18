<?php

namespace App\Http\Requests\PayFrequency;

use Illuminate\Foundation\Http\FormRequest;

class BasePayFrequencyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'time_period_preset_id' => 'nullable|numeric',
            'period' => 'nullable|array',
            'cutoff_type' => 'nullable|numeric',
            'cut_off_value' => 'nullable|numeric',
            'days_span' => 'nullable|numeric',
        ];
    }
}
