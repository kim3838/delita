<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\BindingsServiceProvider::class,
    App\Providers\DeferrableBindingsServiceProvider::class,
    App\Providers\FormulaBindingsServiceProvider::class,
    App\Providers\ImportBindingsServiceProvider::class,
    App\Providers\MysqlBootProvider::class,
    App\Providers\ObserverServiceProvider::class,
    App\Providers\RateLimiterProvider::class,
    App\Providers\RepositoryBindingsServiceProvider::class,
    App\Providers\TransformerServiceProvider::class,
    App\Providers\UtilityServiceProvider::class,
    Maatwebsite\Excel\ExcelServiceProvider::class,
];
