<?php

namespace App\Models;

use App\Casts\Parsable;
use App\Enums\CutOffType;
use App\Enums\PayFrequency as PayFrequencyEnum;
use App\Enums\WeekDay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayFrequency extends Model
{
    protected $fillable = [
        'company_id',
        'code',
        'order',
        'type',
        'time_period_preset_id',
        'period',
        'cutoff_type',
        'cut_off_value',
        'days_span'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'int',
        'ulid' => 'string',
        'company_id' => 'int',
        'code' => 'string',
        'order' => 'int',
        'type' => PayFrequencyEnum::class,
        'time_period_preset_id' => 'int',
        'period' => Parsable::class,
        'cutoff_type' => CutOffType::class,
        'cut_off_value' => WeekDay::class,
        'days_span' => 'int',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function timePeriodPreset(): BelongsTo
    {
        return $this->belongsTo(TimePeriodPreset::class, 'time_period_preset_id', 'id');
    }
}
