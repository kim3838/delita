<?php

namespace App\Http\Requests\OvertimeRequest;

use App\Models\OvertimeRequest;

class StoreOvertimeRequestRequest extends BaseStoreOvertimeRequestRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', OvertimeRequest::class);
    }
}
