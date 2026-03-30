<?php

namespace App\Models\Hydrations;

use App\Enums\CompanyUserAssignmentType;
use Illuminate\Database\Eloquent\Model;

class AssociatedCompany extends Model
{
    protected $casts = [
        'user_id' => 'int',
        'company_id' => 'int',
        'company_ulid' => 'string',
        'account_id' => 'int',
        'account_number' => 'string',
        'company_code' => 'string',
        'company_short_name' => 'string',
        'company_name' => 'string',
        'company_address_line_1' => 'string',
        'company_address_line_2' => 'string',
        'company_city' => 'string',
        'company_state' => 'string',
        'company_postal_code' => 'string',
        'country_id' => 'int',
        'country_name' => 'string',
        'company_currency' => 'string',
        'company_timezone' => 'string',
        'assignment_type' => CompanyUserAssignmentType::class,
    ];
}
