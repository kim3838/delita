<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CompanyUser extends Pivot
{
    protected $fillable = [
        'user_id',
        'company_id',
        'assignment_type'
    ];
}
