<?php

namespace App\Http\Requests\JsonPreset;

use App\Models\JsonPreset;
use Illuminate\Foundation\Http\FormRequest;

class ListJsonPresetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', JsonPreset::class);
    }
}
