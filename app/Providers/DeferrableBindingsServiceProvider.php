<?php

namespace App\Providers;

use App\Blueprint\EnumInterface;
use App\Concrete\EnumConcrete;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class DeferrableBindingsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public $bindings = [
        'enum' => EnumConcrete::class,
        EnumInterface::class => EnumConcrete::class,
    ];

    public function register(): void
    {

    }

    public function provides(): array
    {
        return [
            'enum',
            EnumInterface::class,
        ];
    }
}
