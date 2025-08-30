<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\EmployeePayrollComponentRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Models\Employee;
use App\Models\EmployeePayrollComponent;

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

    public function compensations($employeeUlid)
    {
        return Employee::query()->where('ulid', $employeeUlid)->firstOrFail()->compensations;
    }

    public function deductions($employeeUlid)
    {
        return Employee::query()->where('ulid', $employeeUlid)->firstOrFail()->deductions;
    }

    public function incomeTaxes($employeeUlid)
    {
        return Employee::query()->where('ulid', $employeeUlid)->firstOrFail()->incomeTaxes;
    }
}
