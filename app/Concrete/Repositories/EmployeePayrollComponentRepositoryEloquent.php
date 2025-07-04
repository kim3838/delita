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

    public function compensations($employeeUlid)
    {
        return Employee::where('ulid', $employeeUlid)->firstOrFail()->compensations;
    }

    public function deductions($employeeUlid)
    {
        return Employee::where('ulid', $employeeUlid)->firstOrFail()->deductions;
    }

    public function incomeTaxes($employeeUlid)
    {
        return Employee::where('ulid', $employeeUlid)->firstOrFail()->incomeTaxes;
    }
}
