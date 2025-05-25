<?php

namespace App\Providers;

use App\Blueprint\Repositories\AssociatedCompanyRepository;
use App\Blueprint\Repositories\CompanyCompensationRepository;
use App\Blueprint\Repositories\CompanyFormulaRepository;
use App\Blueprint\Repositories\CompensationRepository;
use App\Blueprint\Repositories\PrototypeRepository;
use App\Concrete\Repositories\AssociatedCompanyRepositoryEloquent;
use App\Concrete\Repositories\CompanyCompensationRepositoryEloquent;
use App\Concrete\Repositories\CompanyFormulaRepositoryEloquent;
use App\Concrete\Repositories\CompensationRepositoryEloquent;
use App\Concrete\Repositories\PrototypeRepositoryEloquent;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public $bindings = [
        'prototype' => PrototypeRepositoryEloquent::class,
        'associated_company' => AssociatedCompanyRepositoryEloquent::class,
        'formula' => CompanyFormulaRepositoryEloquent::class,
        'compensation' => CompensationRepositoryEloquent::class,
        'company_compensation' => CompanyCompensationRepositoryEloquent::class,
        PrototypeRepository::class => PrototypeRepositoryEloquent::class,
        AssociatedCompanyRepository::class => AssociatedCompanyRepositoryEloquent::class,
        CompanyFormulaRepository::class => CompanyFormulaRepositoryEloquent::class,
        CompensationRepository::class => CompensationRepositoryEloquent::class,
        CompanyCompensationRepository::class => CompanyCompensationRepositoryEloquent::class,
    ];

    public function provides()
    {
        return [
            'prototype',
            'associated_company',
            'formula',
            'compensation',
            'company_compensation',
            PrototypeRepository::class,
            AssociatedCompanyRepository::class,
            CompanyFormulaRepository::class,
            CompensationRepository::class,
            CompanyCompensationRepository::class,
        ];
    }
}
