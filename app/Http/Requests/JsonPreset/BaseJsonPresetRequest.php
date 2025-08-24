<?php

namespace App\Http\Requests\JsonPreset;

use Illuminate\Foundation\Http\FormRequest;

class BaseJsonPresetRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'path' => 'required|string',
        ];
    }

}
