<?php

namespace App\Models\Hydrations\Group;

use App\Enums\GroupType;
use App\Models\Group;

class EmployeeGroup extends Group
{
    protected $casts = [
        'type' => GroupType::class,
    ];
}
