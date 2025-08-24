<?php

namespace App\Http\Requests\JsonPreset;

use App\Models\JsonPreset;
use Illuminate\Validation\Rule;

class UpdateJsonPresetRequest extends BaseJsonPresetRequest
{
    public function authorize(): bool
    {
        $jsonPreset = JsonPreset::findOrfail($this->route('jsonPresetId'));

        return $this->user()->can('update', $jsonPreset);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'key' => [
                'required',
                'string',
                'max:255',
                Rule::unique('json_presets')->ignore($this->route('jsonPresetId'))
            ],
            'json_file' => 'sometimes|required|file|mimes:json|mimetypes:application/json,text/json',
        ]);
    }
}
