<?php

namespace App\Models\Hydrations;

use App\Enums\CompanyUserAssignmentType;
use Illuminate\Database\Eloquent\Model;

class AssociatedCompany extends Model
{
    protected $casts = [
        'company_id' => 'int',
        'company_name' => 'string',
        'assignment_type' => CompanyUserAssignmentType::class,
    ];
}
