<?php

namespace App\Http\Requests\ApprovalSetting;

use App\Models\ApprovalSetting;
use Illuminate\Foundation\Http\FormRequest;

class ListApprovalSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', ApprovalSetting::class);
    }
}
