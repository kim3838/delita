<?php

namespace App\Models\Hydrations\User;

use Illuminate\Database\Eloquent\Model;

class CompanyUserRolePermission extends Model
{
    protected $casts = [
        'permission' => 'string',
        'permitted' => 'boolean'
    ];
}
