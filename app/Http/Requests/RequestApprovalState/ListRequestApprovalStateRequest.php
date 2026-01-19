<?php

namespace App\Http\Requests\RequestApprovalState;

use App\Models\RequestApprovalState;
use Illuminate\Foundation\Http\FormRequest;

class ListRequestApprovalStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', RequestApprovalState::class);
    }
}
