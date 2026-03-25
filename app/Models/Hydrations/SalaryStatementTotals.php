<?php

namespace App\Models\Hydrations;

use Illuminate\Database\Eloquent\Model;

class SalaryStatementTotals extends Model
{
    protected $casts = [
        'company_currency_code' => 'string',
        'basic_gross' => 'decimal:6',
        'taxable' => 'decimal:6',
        'withholding_tax' => 'decimal:6',
        'tax_refund' => 'decimal:6',
        'net' => 'decimal:6',
    ];
}
