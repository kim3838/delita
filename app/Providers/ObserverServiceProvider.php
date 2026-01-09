<?php

namespace App\Providers;

use App\Observers\AccountObserver;
use App\Observers\EmployeeObserver;
use App\Observers\HasUlid;
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
                    Relation::getMorphedModel('account'),
                ],
                'observers' => [
                    AccountObserver::class,
                ]
            ),
            array(
                'observables' => [
                    Relation::getMorphedModel('compensation'),
                    Relation::getMorphedModel('deduction'),
                    Relation::getMorphedModel('income_tax'),
                    Relation::getMorphedModel('salary_statement_module'),
                ],
                'observers' => [
                    OrderableObserver::class
                ]
            ),
            array(
                'observables' => [
                    Relation::getMorphedModel('user'),
                    Relation::getMorphedModel('role'),
                    Relation::getMorphedModel('company'),
                    Relation::getMorphedModel('shift'),
                    Relation::getMorphedModel('pay_frequency'),
                    Relation::getMorphedModel('formula'),
                    Relation::getMorphedModel('group'),
                    Relation::getMorphedModel('attendance'),
                    Relation::getMorphedModel('attendance_detail'),
                    Relation::getMorphedModel('overtime'),
                    Relation::getMorphedModel('holiday'),
                    Relation::getMorphedModel('leave_type'),
                    Relation::getMorphedModel('leave'),
                    Relation::getMorphedModel('leave_balance_adjustment'),
                ],
                'observers' => [
                    HasUlid::class,
                ]
            ),
            array(
                'observables' => [
                    Relation::getMorphedModel('employee'),
                ],
                'observers' => [
                    EmployeeObserver::class,
                ]
            ),
        );
    }
}
