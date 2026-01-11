<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = User::query()->findOrfail($this->route('userId'));

        return $this->user()->can('update', $user);
    }

    public function rules(): array
    {
        return [
            'status' => 'required|numeric',
            'timezone' => 'required|string',
            'role_ids' => 'array',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status is required',
            'timezone.required' => 'Timezone is required',
        ];
    }
}
