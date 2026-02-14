<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\SalaryStatementModuleRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\Formulable;
use App\Models\SalaryStatementModule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalaryStatementModuleRepositoryEloquent extends BaseRepositoryEloquent implements SalaryStatementModuleRepository
{
    public function model(): string
    {
        return SalaryStatementModule::class;
    }

    public function list($filters): Collection
    {
        $queryBuilder = $this->model::query()->getQuery()
            ->when($filters->company_id ?? false, function ($builder, $value) {
                $builder->where(DB::raw("company_id"), $value);
            })
            ->orderBy('order', 'ASC');

        return $this->hydrateCollection($queryBuilder->get(), $this->model());
    }

    public static function defaultPresets(): array
    {
        return [
            [
                'order' => 1,
                'key' => 'basic_pay_allowances_and_overtime',
                'name' => 'Basic pay, allowances, and overtime',
                'formulable_type' => Formulable::EARNINGS,
                'statement_level' => false,
                'aggregation' => false,
                'property' => 'employee',
                'attribute' => 'compensations',
                'conditions' => null
            ],
            [
                'order' => 2,
                'key' => 'other_earnings',
                'name' => 'Other earnings',
                'formulable_type' => Formulable::EARNINGS,
                'statement_level' => true,
                'aggregation' => false,
                'property' => 'company',
                'attribute' => 'compensations',
                'conditions' => null
            ],
            [
                'order' => 3,
                'key' => 'assigned_deductions',
                'name' => 'Assigned deductions',
                'formulable_type' => Formulable::DEDUCTIONS,
                'statement_level' => true,
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
                'order' => 4,
                'key' => 'taxable_income',
                'name' => 'Taxable income',
                'formulable_type' => Formulable::TAXABLE_INCOME,
                'statement_level' => true,
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
                'order' => 5,
                'key' => 'non_taxable_income',
                'name' => 'Non-taxable income',
                'formulable_type' => Formulable::NONTAXABLE_INCOME,
                'statement_level' => true,
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
                'order' => 6,
                'key' => 'assigned_income_taxes',
                'name' => 'Assigned income taxes',
                'formulable_type' => Formulable::INCOME_TAX,
                'statement_level' => true,
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
                'order' => 7,
                'key' => 'net_income',
                'name' => 'Net income',
                'formulable_type' => Formulable::NET_INCOME,
                'statement_level' => true,
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
