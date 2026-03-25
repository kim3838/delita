<?php

namespace App\Models\Hydrations;

use Illuminate\Database\Eloquent\Model;

class PayrollTotals extends Model
{
    protected $casts = [
        'company_currency_code' => 'string',
        'employer_contribution_share' => 'decimal:6',
        'withholding_tax' => 'decimal:6',
        'tax_refund' => 'decimal:6',
        'net' => 'decimal:6',
    ];
}
