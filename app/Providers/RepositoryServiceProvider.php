<?php

namespace App\Providers;

use App\Blueprint\Repositories\AssociatedCompanyRepository;
use App\Blueprint\Repositories\FormulaRepository;
use App\Blueprint\Repositories\PrototypeRepository;
use App\Concrete\Repositories\AssociatedCompanyRepositoryEloquent;
use App\Concrete\Repositories\FormulaRepositoryEloquent;
use App\Concrete\Repositories\PrototypeRepositoryEloquent;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public $bindings = [
        'prototype' => PrototypeRepositoryEloquent::class,
        'associated_company' => AssociatedCompanyRepositoryEloquent::class,
        'formula' => FormulaRepositoryEloquent::class,
        PrototypeRepository::class => PrototypeRepositoryEloquent::class,
        AssociatedCompanyRepository::class => AssociatedCompanyRepositoryEloquent::class,
        FormulaRepository::class => FormulaRepositoryEloquent::class,
    ];

    public function provides()
    {
        return [
            'prototype',
            'associated_company',
            'formula',
            PrototypeRepository::class,
            AssociatedCompanyRepository::class,
            FormulaRepository::class,
        ];
    }
}
