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
        'key',
        'name',
        'formulable_type',
        'statement_level',
        'aggregation',
        'property',
        'attribute',
        'conditions'
    ];

    protected $casts = [
        'company_id' => 'int',
        'order' => 'int',
        'key' => 'string',
        'name' => 'string',
        'formulable_type' => Formulable::class,
        'statement_level' => 'boolean',
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
