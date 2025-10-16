<?php

namespace App\Http\Requests\EmployeeGroup;

use App\Http\Requests\Group\BaseGroupableRequest;
use App\Models\Group;

class DetachAssignedGroupsRequest extends BaseGroupableRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('detachAssignedGroup', Group::class);
    }
}
