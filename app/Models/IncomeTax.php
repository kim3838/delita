<?php

namespace App\Models;

use App\Enums\IncomeTax as IncomeTaxEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomeTax extends Model
{
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
        'id' => 'int',
        'company_id' => 'int',
        'name' => 'string',
        'order' => 'int',
        'assignable' => 'boolean',
        'type' => IncomeTaxEnum::class,
        'company_formula_id' => 'int',
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
