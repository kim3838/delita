<?php

namespace App\Providers;

use App\Blueprint\Imports\EmployeeImport;
use App\Blueprint\Imports\EmployeePayrollComponentImport;
use App\Blueprint\Imports\EmploymentProfileImport;
use App\Concrete\Imports\EmployeeImportConcrete;
use App\Concrete\Imports\EmployeePayrollComponentImportConcrete;
use App\Concrete\Imports\EmploymentProfileImportConcrete;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ImportBindingsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public $bindings = [
        'employee_import' => EmployeeImportConcrete::class,
        'employment_profile_import' => EmploymentProfileImportConcrete::class,
        'employee_payroll_component_import' => EmployeePayrollComponentImportConcrete::class,
        EmployeeImport::class => EmployeeImportConcrete::class,
        EmploymentProfileImport::class => EmploymentProfileImportConcrete::class,
        EmployeePayrollComponentImport::class => EmployeePayrollComponentImportConcrete::class,
    ];

    public function provides(): array
    {
        return [
            'employee_import',
            'employment_profile_import',
            'employee_payroll_component_import',
            EmployeeImport::class,
            EmploymentProfileImport::class,
            EmployeePayrollComponentImport::class,
        ];
    }
}
