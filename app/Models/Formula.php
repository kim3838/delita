<?php

namespace App\Models;

use App\Casts\FormulaComponentType;
use App\Enums\Formulable;
use Illuminate\Database\Eloquent\Model;

class Formula extends Model
{
    protected $fillable = [
        'name',
        'formulable_type',
        'component_type',
        'interpolation'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'formulable_type' => Formulable::class,
        'component_type' => FormulaComponentType::class,
        'interpolation' => 'boolean'
    ];

    public function companies()
    {
        return $this->belongsToMany(Company::class)
            ->withTimestamps();
    }
}
