<?php

namespace App\Models;

use App\Casts\Parsable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayPeriodSetting extends Model
{
    protected $fillable = [
        'company_id',
        'days_to_pay_after_cut_off',
        'monthly_pay_period',
        'semimonthly_pay_period',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'company_id' => 'int',
        'days_to_pay_after_cut_off' => 'int',
        'monthly_pay_period' => Parsable::class,
        'semimonthly_pay_period' => Parsable::class,
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
