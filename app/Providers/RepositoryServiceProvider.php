<?php

namespace App\Providers;

use App\Blueprint\Repositories\AssociatedCompanyRepository;
use App\Blueprint\Repositories\CompanyCompensationRepository;
use App\Blueprint\Repositories\CompanyDeductionRepository;
use App\Blueprint\Repositories\CompanyFormulaRepository;
use App\Blueprint\Repositories\CompensationRepository;
use App\Blueprint\Repositories\DeductionRepository;
use App\Blueprint\Repositories\PrototypeRepository;
use App\Concrete\Repositories\AssociatedCompanyRepositoryEloquent;
use App\Concrete\Repositories\CompanyCompensationRepositoryEloquent;
use App\Concrete\Repositories\CompanyDeductionRepositoryEloquent;
use App\Concrete\Repositories\CompanyFormulaRepositoryEloquent;
use App\Concrete\Repositories\CompensationRepositoryEloquent;
use App\Concrete\Repositories\DeductionRepositoryEloquent;
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
        'deduction' => DeductionRepositoryEloquent::class,
        'company_deduction' => CompanyDeductionRepositoryEloquent::class,
        PrototypeRepository::class => PrototypeRepositoryEloquent::class,
        AssociatedCompanyRepository::class => AssociatedCompanyRepositoryEloquent::class,
        CompanyFormulaRepository::class => CompanyFormulaRepositoryEloquent::class,
        CompensationRepository::class => CompensationRepositoryEloquent::class,
        CompanyCompensationRepository::class => CompanyCompensationRepositoryEloquent::class,
        DeductionRepository::class => DeductionRepositoryEloquent::class,
        CompanyDeductionRepository::class => CompanyDeductionRepositoryEloquent::class,
    ];

    public function provides(): array
    {
        return [
            'prototype',
            'associated_company',
            'formula',
            'compensation',
            'company_compensation',
            'deduction',
            'company_deduction',
            PrototypeRepository::class,
            AssociatedCompanyRepository::class,
            CompanyFormulaRepository::class,
            CompensationRepository::class,
            CompanyCompensationRepository::class,
            DeductionRepository::class,
            CompanyDeductionRepository::class,
        ];
    }
}
