<?php

namespace App\Models;

use App\Enums\GroupType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Group extends Model
{
    protected $fillable = [
        'company_id',
        'type',
        'name'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'type' => GroupType::class,
    ];

    public function employees(): MorphToMany
    {
        return $this->morphedByMany(Employee::class, 'groupable');
    }
}
