<?php

namespace App\Models;

use App\Enums\Formulable;
use App\Enums\FormulableComponentSubType;
use App\Enums\IncomeTax as IncomeTaxEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class IncomeTax extends Model
{
    protected $fillable = [
        'company_id',
        'code',
        'name',
        'order',
        'assignable',
        'type',
        'component_sub_type',
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
        'type' => IncomeTaxEnum::class,
        'component_sub_type' => FormulableComponentSubType::class,
        'company_formula_id' => 'int',
    ];

    protected $appends = [
        'formulable_type',
    ];

    public function formulableType(): Attribute
    {
        return new Attribute(get: fn () => Formulable::INCOME_TAX);
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
