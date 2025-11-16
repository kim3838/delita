<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmployeePayrollComponentRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Facades\Fractal;
use App\Models\Employee;
use App\Models\EmployeePayrollComponent;
use App\Transformers\EmployeePayrollComponent\PatchableTransformer;

class EmployeePayrollComponentRepositoryEloquent extends BaseRepositoryEloquent implements EmployeePayrollComponentRepository
{
    public function model(): string
    {
        return EmployeePayrollComponent::class;
    }

    public function list($employeeUlid)
    {
        $employee = Employee::query()->where('ulid', $employeeUlid)->firstOrFail();

        $compensations = $employee->compensations;
        $deductions = $employee->deductions;
        $incomeTaxes = $employee->incomeTaxes;

        return [
            'compensations' => $compensations,
            'deductions' => $deductions,
            'income_taxes' => $incomeTaxes,
        ];
    }

    public function store($attributes)
    {
        $hydrated = $this->hydrateItem($attributes);
        $patchable = Fractal::item($hydrated, PatchableTransformer::class);

        return $this->model::create($patchable);
    }

    public function update($id, $attributes)
    {
        $model = $this->model::findOrfail($id);

        $hydrated = $this->hydrateItem($attributes);
        $patchable = Fractal::item($hydrated, PatchableTransformer::class);

        $model->update($patchable);

        return $model;
    }
}
