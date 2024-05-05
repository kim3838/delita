<?php

namespace App\Providers;

use App\Blueprint\Repositories\PrototypeRepository;
use App\Concrete\Repositories\PrototypeRepositoryEloquent;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public $bindings = [
        'prototype' => PrototypeRepositoryEloquent::class,
        PrototypeRepository::class => PrototypeRepositoryEloquent::class
    ];

    public function provides()
    {
        return [
            'prototype',
            PrototypeRepository::class
        ];
    }
}
