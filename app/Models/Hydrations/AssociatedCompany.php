<?php

namespace App\Models\Hydrations;

use App\Enums\CompanyUserAssignmentType;
use Illuminate\Database\Eloquent\Model;

class AssociatedCompany extends Model
{
    protected $casts = [
        'company_id' => 'int',
        'company_ulid' => 'string',
        'account_number' => 'string',
        'company_code' => 'string',
        'company_name' => 'string',
        'country_name' => 'string',
        'company_currency' => 'string',
        'company_timezone' => 'string',
        'assignment_type' => CompanyUserAssignmentType::class,
    ];
}
