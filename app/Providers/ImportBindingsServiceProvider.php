<?php

namespace App\Providers;

use App\Blueprint\Imports\EmployeeImport;
use App\Concrete\Imports\EmployeeImportConcrete;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ImportBindingsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public $bindings = [
        'employee_import' => EmployeeImportConcrete::class,
        EmployeeImport::class => EmployeeImportConcrete::class,
    ];

    public function provides(): array
    {
        return [
            'employee_import',
            EmployeeImport::class,
        ];
    }
}
