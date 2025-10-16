<?php

namespace App\Http\Requests\EmployeeGroup;

use App\Http\Requests\Group\BaseGroupableRequest;
use App\Models\Group;

class BatchDestroyEmployeeGroupRequest extends BaseGroupableRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('batchDelete', Group::class);
    }
}
