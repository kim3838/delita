<?php

namespace App\Providers;

use App\Blueprint\Imports\AttendanceImport;
use App\Blueprint\Imports\EmployeeIdentificationImport;
use App\Blueprint\Imports\EmployeeImport;
use App\Blueprint\Imports\EmployeePayrollComponentImport;
use App\Blueprint\Imports\EmploymentProfileImport;
use App\Blueprint\Imports\OvertimeImport;
use App\Concrete\Imports\AttendanceImportConcrete;
use App\Concrete\Imports\EmployeeIdentificationImportConcrete;
use App\Concrete\Imports\EmployeeImportConcrete;
use App\Concrete\Imports\EmployeePayrollComponentImportConcrete;
use App\Concrete\Imports\EmploymentProfileImportConcrete;
use App\Concrete\Imports\OvertimeImportConcrete;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ImportBindingsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public $bindings = [
        'employee_import' => EmployeeImportConcrete::class,
        'employment_profile_import' => EmploymentProfileImportConcrete::class,
        'employee_identification_import' => EmployeeIdentificationImportConcrete::class,
        'employee_payroll_component_import' => EmployeePayrollComponentImportConcrete::class,
        'attendance_import' => AttendanceImportConcrete::class,
        'overtime_import' => OvertimeImportConcrete::class,
        EmployeeImport::class => EmployeeImportConcrete::class,
        EmploymentProfileImport::class => EmploymentProfileImportConcrete::class,
        EmployeeIdentificationImport::class => EmployeeIdentificationImportConcrete::class,
        EmployeePayrollComponentImport::class => EmployeePayrollComponentImportConcrete::class,
        AttendanceImport::class => AttendanceImportConcrete::class,
        OvertimeImport::class => OvertimeImportConcrete::class,
    ];

    public function provides(): array
    {
        return [
            'employee_import',
            'employment_profile_import',
            'employee_identification_import',
            'employee_payroll_component_import',
            'attendance_import',
            'overtime_import',
            EmployeeImport::class,
            EmploymentProfileImport::class,
            EmployeeIdentificationImport::class,
            EmployeePayrollComponentImport::class,
            AttendanceImport::class,
            OvertimeImport::class,
        ];
    }
}
