<?php

namespace App\Models;

use App\Enums\EmploymentStatus;
use App\Enums\EmploymentType;
use App\Enums\EndOfServiceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmploymentProfile extends Model
{
    protected $fillable = [
        'employee_id',
        'status',
        'employment_type',
        'start_date',
        'end_of_service_type',
        'end_date',
    ];

    protected $casts = [
        'employee_id' => 'int',
        'status' => EmploymentStatus::class,
        'employment_type' => EmploymentType::class,
        'start_date' => 'datetime',
        'end_of_service_type' => EndOfServiceType::class,
        'end_date' => 'datetime',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
