<?php

namespace App\Models;

use App\Enums\CompanyUserAssignmentType;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CompanyUser extends Pivot
{
    protected $fillable = [
        'user_id',
        'company_id',
        'assignment_type'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'assignment_type' => CompanyUserAssignmentType::class,
    ];
}
