<?php

namespace App\Models\Hydrations;

use Illuminate\Database\Eloquent\Model;

class PayrollTotals extends Model
{
    protected $casts = [
        'employer_contribution_share' => 'decimal:6',
        'withholding_tax' => 'decimal:6',
        'net' => 'decimal:6',
    ];
}
