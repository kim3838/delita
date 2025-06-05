<?php

namespace App\Providers;

use App\Observers\EmployeeObserver;
use App\Observers\OrderableObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        foreach ($this->observableMap() as $map) {
            foreach ($map['observables'] as $observable){
                call_user_func_array(array($observable, 'observe'), array($map['observers']));
            }
        }
    }

    protected function observableMap(): array
    {
        return array(
            array(
                'observables' => [
                    Relation::getMorphedModel('employee'),
                ],
                'observers' => [
                    EmployeeObserver::class,
                ]
            ),
            array(
                'observables' => [
                    Relation::getMorphedModel('compensation'),
                    Relation::getMorphedModel('deduction'),
                    Relation::getMorphedModel('income_tax'),
                ],
                'observers' => [
                    OrderableObserver::class
                ]
            )
        );
    }
}
