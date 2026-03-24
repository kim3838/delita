<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalTaxHistory extends Model
{
    protected $fillable = [
        'employee_id',

        'year',
        'total_taxable',
        'total_nontaxable_bonus',
        'total_taxable_from_bonus',
        'total_tax_withheld',
        'remarks',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'ulid' => 'string',
        'employee_id' => 'int',

        'year' => 'int',
        'total_taxable' => 'decimal:6',
        'total_nontaxable_bonus' => 'decimal:6',
        'total_taxable_from_bonus' => 'decimal:6',
        'total_tax_withheld' => 'decimal:6',
        'remarks' => 'string',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
