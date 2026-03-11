<?php

namespace App\Models;

use App\Enums\IdentificationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeIdentification extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'number',
        'readable_number',
    ];

    protected $casts = [
        'employee_id' => 'int',
        'type' => IdentificationType::class,
        'number' => 'string',
        'readable_number' => 'string',
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
