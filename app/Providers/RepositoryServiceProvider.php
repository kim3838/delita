<?php

namespace App\Providers;

use App\Blueprint\Repositories\AssociatedCompanyRepository;
use App\Blueprint\Repositories\CompanyCompensationRepository;
use App\Blueprint\Repositories\CompanyDeductionRepository;
use App\Blueprint\Repositories\CompanyFormulaRepository;
use App\Blueprint\Repositories\CompanyIncomeTaxRepository;
use App\Blueprint\Repositories\CompanyRepository;
use App\Blueprint\Repositories\CompensationRepository;
use App\Blueprint\Repositories\DeductionRepository;
use App\Blueprint\Repositories\IncomeTaxRepository;
use App\Blueprint\Repositories\PayPeriodSettingRepository;
use App\Blueprint\Repositories\PrototypeRepository;
use App\Blueprint\Repositories\TimePeriodPresetRepository;
use App\Concrete\Repositories\AssociatedCompanyRepositoryEloquent;
use App\Concrete\Repositories\CompanyCompensationRepositoryEloquent;
use App\Concrete\Repositories\CompanyDeductionRepositoryEloquent;
use App\Concrete\Repositories\CompanyFormulaRepositoryEloquent;
use App\Concrete\Repositories\CompanyIncomeTaxRepositoryEloquent;
use App\Concrete\Repositories\CompanyRepositoryEloquent;
use App\Concrete\Repositories\CompensationRepositoryEloquent;
use App\Concrete\Repositories\DeductionRepositoryEloquent;
use App\Concrete\Repositories\IncomeTaxRepositoryEloquent;
use App\Concrete\Repositories\PayPeriodSettingRepositoryEloquent;
use App\Concrete\Repositories\PrototypeRepositoryEloquent;
use App\Concrete\Repositories\TimePeriodPresetRepositoryEloquent;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public $bindings = [
        'prototype' => PrototypeRepositoryEloquent::class,
        'company' => CompanyRepositoryEloquent::class,
        'associated_company' => AssociatedCompanyRepositoryEloquent::class,
        'formula' => CompanyFormulaRepositoryEloquent::class,
        'compensation' => CompensationRepositoryEloquent::class,
        'company_compensation' => CompanyCompensationRepositoryEloquent::class,
        'deduction' => DeductionRepositoryEloquent::class,
        'company_deduction' => CompanyDeductionRepositoryEloquent::class,
        'income_tax' => IncomeTaxRepositoryEloquent::class,
        'company_income_tax' => CompanyIncomeTaxRepositoryEloquent::class,
        'pey_period_setting' => PayPeriodSettingRepositoryEloquent::class,
        'time_period_preset' => TimePeriodPresetRepositoryEloquent::class,
        PrototypeRepository::class => PrototypeRepositoryEloquent::class,
        AssociatedCompanyRepository::class => AssociatedCompanyRepositoryEloquent::class,
        CompanyFormulaRepository::class => CompanyFormulaRepositoryEloquent::class,
        CompensationRepository::class => CompensationRepositoryEloquent::class,
        CompanyCompensationRepository::class => CompanyCompensationRepositoryEloquent::class,
        DeductionRepository::class => DeductionRepositoryEloquent::class,
        CompanyDeductionRepository::class => CompanyDeductionRepositoryEloquent::class,
        IncomeTaxRepository::class => IncomeTaxRepositoryEloquent::class,
        CompanyIncomeTaxRepository::class => CompanyIncomeTaxRepositoryEloquent::class,
        PayPeriodSettingRepository::class => PayPeriodSettingRepositoryEloquent::class,
        TimePeriodPresetRepository::class => TimePeriodPresetRepositoryEloquent::class,
        CompanyRepository::class => CompanyRepositoryEloquent::class,
    ];

    public function provides(): array
    {
        return [
            'prototype',
            'company',
            'associated_company',
            'formula',
            'compensation',
            'company_compensation',
            'deduction',
            'company_deduction',
            'income_tax',
            'company_income_tax',
            'pey_period_setting',
            'time_period_preset',
            PrototypeRepository::class,
            CompanyRepository::class,
            AssociatedCompanyRepository::class,
            CompanyFormulaRepository::class,
            CompensationRepository::class,
            CompanyCompensationRepository::class,
            DeductionRepository::class,
            CompanyDeductionRepository::class,
            IncomeTaxRepository::class,
            CompanyIncomeTaxRepository::class,
            PayPeriodSettingRepository::class,
            TimePeriodPresetRepository::class,
        ];
    }
}
