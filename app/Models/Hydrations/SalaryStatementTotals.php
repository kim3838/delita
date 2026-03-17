<?php

namespace App\Models\Hydrations;

use Illuminate\Database\Eloquent\Model;

class SalaryStatementTotals extends Model
{
    protected $casts = [
        'total_basic_gross' => 'decimal:6',
        'total_taxable' => 'decimal:6',
        'total_withholding_tax' => 'decimal:6',
    ];
}
