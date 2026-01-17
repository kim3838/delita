<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = [
        'company_id',
        'parent_id',
        'name',
    ];

    protected $casts = [
        'id' => 'int',
        'company_id' => 'int',
        'parent_id' => 'int',
        'name' => 'string',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // Parent department
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    // Sub-departments
    public function subDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class)
            ->using(DepartmentEmployee::class)
            ->withPivot(['id', 'department_assignment_type'])
            ->withTimestamps();
    }
}
