<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Group extends Model
{
    protected $fillable = [
        'company_id',
        'name'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function employees(): MorphToMany
    {
        return $this->morphedByMany(Employee::class, 'groupable');
    }
}
