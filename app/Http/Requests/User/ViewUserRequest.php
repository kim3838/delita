<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ViewUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = User::query()->where('ulid', $this->route('ulid'))->firstOrfail();

        return $this->user()->can('view', $user);
    }
}
