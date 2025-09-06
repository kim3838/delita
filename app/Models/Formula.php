<?php

namespace App\Models;

use App\Casts\FormulaComponentType;
use App\Casts\Parsable;
use App\Enums\Formulable;
use Illuminate\Database\Eloquent\Model;

class Formula extends Model
{
    protected $fillable = [
        'name',
        'formulable_type',
        'component_type',
        'aggregation',
        'default_settings'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'ulid' => 'string',
        'name' => 'string',
        'formulable_type' => Formulable::class,
        'component_type' => FormulaComponentType::class,
        'aggregation' => 'boolean',
        'default_settings' => Parsable::class
    ];

    public function companies()
    {
        return $this->belongsToMany(Company::class)
            ->withPivot('settings')
            ->withTimestamps();
    }
}
