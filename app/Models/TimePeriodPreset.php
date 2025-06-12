<?php

namespace App\Models;

use App\Enums\TimePeriodType;
use Illuminate\Database\Eloquent\Model;

class TimePeriodPreset extends Model
{
    protected $fillable = [
        'type',
        'name',
        'readable_name',
        'yearly_period',
        'monthly_period',
        'semimonthly_pay_period',
    ];

    protected $casts = [
        'type' => TimePeriodType::class,
        'name' => 'string',
        'readable_name' => 'string',
        'yearly_period' => 'array',
        'monthly_period' => 'array',
        'semimonthly_period' => 'array',
    ];
}
