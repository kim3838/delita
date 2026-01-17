<?php

namespace App\Models;

use App\Enums\DepartmentEmployeeAssignmentType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DepartmentEmployee extends Pivot
{
    protected $table = 'department_employee';

    protected $fillable =[
        'department_id',
        'employee_id',
        'department_assignment_type'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'department_id' => 'int',
        'employee_id' => 'int',
        'department_assignment_type' => DepartmentEmployeeAssignmentType::class
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
