<?php

namespace App\Models\Hydrations\User;

use App\Enums\CompanyUserAssignmentType;
use App\Enums\RequestApprovalStatus;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Model;

class UserFiledRequest extends Model
{
    protected $casts = [
        'id' => 'string',
        'requestable_type' => 'string',
        'requestable_id' => 'int',
        'number' => 'string',
        'date_requested' => 'datetime',
        'reason' => 'string',
        'status_summary' => RequestApprovalStatus::class,

        'user_id' => 'int',
        'user_ulid' => 'string',
        'user_username' => 'string',
        'user_email' => 'string',
        'user_status' => UserStatus::class,
        'user_email_verified_at' => 'datetime:Y-m-d H:i:s',
        'user_timezone' => 'string',

        'user_company_id' => 'int',
        'company_name' => 'string',
        'company_timezone' => 'string',
        'company_assignment_type' => CompanyUserAssignmentType::class,
        'is_employee' => 'boolean',
        'company_employee_number' => 'string',
        'company_employee_family_name' => 'string',
        'company_employee_middle_name' => 'string',
        'company_employee_given_name' => 'string',
    ];
}
