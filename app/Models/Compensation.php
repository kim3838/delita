<?php

namespace App\Models;

use App\Enums\Compensation as CompensationEnum;
use App\Enums\Formulable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Compensation extends Model
{
    protected $table = 'compensations';

    protected $fillable = [
        'company_id',
        'code',
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
        'code' => 'string',
        'name' => 'string',
        'order' => 'int',
        'assignable' => 'boolean',
        'type' => CompensationEnum::class,
        'company_formula_id' => 'int',
    ];

    protected $appends = [
        'formulable_type',
    ];

    public function formulableType(): Attribute
    {
        return new Attribute(get: fn () => Formulable::EARNINGS);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function companyFormula(): BelongsTo
    {
        return $this->belongsTo(CompanyFormula::class);
    }

    public function employeePayrollComponents(): MorphMany
    {
        return $this->morphMany(EmployeePayrollComponent::class, 'payroll_componentable');
    }
}
