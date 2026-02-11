<?php

namespace App\Models;

use App\Enums\HolidayType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holiday extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'type',
        'holiday_pay_forfeiture',
        'date',
        'recurring',
        'active',
        'effective_date',
    ];

    protected $casts = [
        'id' => 'int',
        'company_id' => 'int',
        'name' => 'string',
        'type' => HolidayType::class,
        'holiday_pay_forfeiture' => 'boolean',
        'date' => 'date:Y-m-d',
        'recurring' => 'boolean',
        'active' => 'boolean',
        'effective_date' => 'date:Y-m-d',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
