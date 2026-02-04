<?php

namespace App\Models\Hydrations\Payroll;

use App\Enums\PayFrequency;
use App\Enums\SemiMonthlySequence;
use Illuminate\Database\Eloquent\Model;

class PayrollPayload extends Model
{
    protected $casts = [
        'id' => 'string',
        'year' => 'integer',
        'month' => 'integer',
        'pay_frequency' => PayFrequency::class,
        'frequency_sequence' => SemiMonthlySequence::class,
        'start' => 'date',
        'end' => 'date',
    ];
}
