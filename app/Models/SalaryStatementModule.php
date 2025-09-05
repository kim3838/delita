<?php

namespace App\Models;

use App\Enums\Formulable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryStatementModule extends Model
{
    protected $fillable = [
        'company_id',
        'order',
        'name',
        'formulable_type',
        'aggregation',
        'property',
        'attribute',
        'conditions'
    ];

    protected $casts = [
        'company_id' => 'int',
        'order' => 'int',
        'formulable_type' => Formulable::class,
        'name' => 'string',
        'aggregation' => 'boolean',
        'property' => 'string',
        'reference' => 'string',
        'conditions' => 'array'
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
