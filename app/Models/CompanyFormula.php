<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CompanyFormula extends Pivot
{
    protected $fillable =[
        'formula_id',
        'company_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
