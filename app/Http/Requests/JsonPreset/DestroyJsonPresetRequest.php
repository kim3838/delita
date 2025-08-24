<?php

namespace App\Http\Requests\JsonPreset;

use App\Models\JsonPreset;
use Illuminate\Foundation\Http\FormRequest;

class DestroyJsonPresetRequest extends FormRequest
{
    public function authorize(): bool
    {
        $jsonPreset = JsonPreset::findOrfail($this->route('jsonPresetId'));

        return $this->user()->can('delete', $jsonPreset);
    }
}
