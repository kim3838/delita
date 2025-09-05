<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\SalaryStatementModuleRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\Formulable;
use App\Models\SalaryStatementModule;
use Illuminate\Support\Facades\DB;

class SalaryStatementModuleRepositoryEloquent extends BaseRepositoryEloquent implements SalaryStatementModuleRepository
{
    public function model(): string
    {
        return SalaryStatementModule::class;
    }

    public function list($filters)
    {
        $queryBuilder = $this->model::getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("company_id"), $value);
            })
            ->orderBy('order', 'ASC');

        return $this->model::hydrate($queryBuilder->get()->toArray());
    }

    public static function defaultPresets(): array
    {
        return [
            [
                'order' => 1,
                'name' => 'Assigned Compensations',
                'formulable_type' => Formulable::EARNINGS,
                'aggregation' => false,
                'property' => 'employee',
                'attribute' => 'compensations',
                'conditions' => [
                    [
                        'property' => 'payroll_componentable_type',
                        'operator' => '=',
                        'value' => 'compensation',
                    ],
                    [
                        'property' => 'formulable_type',
                        'operator' => '=',
                        'value' => Formulable::EARNINGS,
                    ],
                ]
            ], [
                'order' => 2,
                'name' => 'Assigned Deductions',
                'formulable_type' => Formulable::DEDUCTIONS,
                'aggregation' => false,
                'property' => 'employee',
                'attribute' => 'deductions',
                'conditions' => [
                    [
                        'property' => 'payroll_componentable_type',
                        'operator' => '=',
                        'value' => 'deduction',
                    ],
                    [
                        'property' => 'formulable_type',
                        'operator' => '=',
                        'value' => Formulable::DEDUCTIONS,
                    ],
                ]
            ],[
                'order' => 3,
                'name' => 'Taxable Income',
                'formulable_type' => Formulable::TAXABLE_INCOME,
                'aggregation' => true,
                'property' => 'company',
                'attribute' => 'formulas',
                'conditions' => [
                    [
                        'property' => 'aggregation',
                        'operator' => '=',
                        'value' => true,
                    ],
                    [
                        'property' => 'formulable_type',
                        'operator' => '=',
                        'value' => Formulable::TAXABLE_INCOME,
                    ],
                ]
            ],[
                'order' => 4,
                'name' => 'Non-Taxable Income',
                'formulable_type' => Formulable::NONTAXABLE_INCOME,
                'aggregation' => true,
                'property' => 'company',
                'attribute' => 'formulas',
                'conditions' => [
                    [
                        'property' => 'aggregation',
                        'operator' => '=',
                        'value' => true,
                    ],
                    [
                        'property' => 'formulable_type',
                        'operator' => '=',
                        'value' => Formulable::NONTAXABLE_INCOME,
                    ],
                ]
            ],[
                'order' => 5,
                'name' => 'Assigned Income Taxes',
                'formulable_type' => Formulable::INCOME_TAX,
                'aggregation' => false,
                'property' => 'employee',
                'attribute' => 'incomeTaxes',
                'conditions' => [
                    [
                        'property' => 'payroll_componentable_type',
                        'operator' => '=',
                        'value' => 'income_tax',
                    ],
                    [
                        'property' => 'formulable_type',
                        'operator' => '=',
                        'value' => Formulable::INCOME_TAX,
                    ],
                ]
            ],[
                'order' => 6,
                'name' => 'Net Income',
                'formulable_type' => Formulable::NET_INCOME,
                'aggregation' => true,
                'property' => 'company',
                'attribute' => 'formulas',
                'conditions' => [
                    [
                        'property' => 'aggregation',
                        'operator' => '=',
                        'value' => true,
                    ],
                    [
                        'property' => 'formulable_type',
                        'operator' => '=',
                        'value' => Formulable::NET_INCOME,
                    ],
                ]
            ],
        ];
    }
}
