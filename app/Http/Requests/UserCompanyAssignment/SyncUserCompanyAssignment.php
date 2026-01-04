<?php

namespace App\Http\Requests\UserCompanyAssignment;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class SyncUserCompanyAssignment extends FormRequest
{
    public function authorize(): bool
    {
        $user = User::query()->findOrfail($this->route('userId'));

        return $this->user()->can('update', $user);
    }
}
