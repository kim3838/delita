<?php

namespace App\Traits;

use App\Blueprint\Repositories\EmployeePayrollComponentRepository;
use App\Models\EmployeePayrollComponent;
use Illuminate\Support\Facades\App;

trait PayrollComponentChain
{
    public function deleteEmployeeAssignedComponentable($componentableType, $componentableId): void
    {
        $employeePayrollComponents = EmployeePayrollComponent::query()
            ->where('payroll_componentable_type', $componentableType)
            ->where('payroll_componentable_id', $componentableId)
            ->get();

        foreach ($employeePayrollComponents as $employeePayrollComponent){

            App::make(EmployeePayrollComponentRepository::class)->delete($employeePayrollComponent->id);
        }
    }
}
