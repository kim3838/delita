<?php

namespace App\Http\Requests\JsonPreset;

use App\Models\JsonPreset;
use Illuminate\Validation\Rule;

class StoreJsonPresetRequest extends BaseJsonPresetRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', JsonPreset::class);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'key' => [
                'required',
                'string',
                'max:255',
                Rule::unique('json_presets')
            ],
            'json_file' => 'required|file|mimes:json|mimetypes:application/json,text/json',
        ]);
    }
}
