<?php

namespace App\Models;

use App\Enums\Deduction as DeductionEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deduction extends Model
{
    protected $table = 'deductions';

    protected $fillable = [
        'company_id',
        'name',
        'order',
        'assignable',
        'type',
        'company_formula_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'assignable' => 'boolean',
        'type' => DeductionEnum::class,
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function companyFormula(): BelongsTo
    {
        return $this->belongsTo(CompanyFormula::class);
    }
}
