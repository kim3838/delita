<?php

namespace App\Concrete\Repositories;

use App\Blueprint\Repositories\SalaryStatementModuleRepository;
use App\Concrete\BaseRepositoryEloquent;
use App\Enums\Compensation;
use App\Enums\Deduction;
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
                'key' => 'statutory_contributions',
                'name' => 'Statutory contributions',
                'formulable_type' => Formulable::DEDUCTIONS,
                'statement_level' => true,
                'aggregation' => false,
                'property' => 'employee',
                'attribute' => 'deductions',
                'conditions' => [
                    [
                        'order' => 1,
                        'property' => 'payroll_componentable_type',
                        'operator' => '=',
                        'value' => 'deduction',
                    ],
                    [
                        'order' => 2,
                        'property' => 'formulable_type',
                        'operator' => '=',
                        'value' => Formulable::DEDUCTIONS,
                    ],
                    [
                        'order' => 3,
                        'property' => [
                            'payrollComponentable',
                            'type',
                            'value'
                        ],
                        'operator' => '=',
                        'value' => Deduction::STATUTORY_CONTRIBUTION,
                    ],
                ]
            ],
            [
                'order' => 3,
                'key' => 'statutory_benefits',
                'name' => 'Statutory Benefits',
                'formulable_type' => Formulable::EARNINGS,
                'statement_level' => true,
                'aggregation' => false,
                'property' => 'employee',
                'attribute' => 'compensations',
                'conditions' => [
                    [
                        'order' => 1,
                        'property' => 'payroll_componentable_type',
                        'operator' => '=',
                        'value' => 'compensation',
                    ],
                    [
                        'order' => 2,
                        'property' => 'formulable_type',
                        'operator' => '=',
                        'value' => Formulable::EARNINGS,
                    ],
                    [
                        'order' => 3,
                        'property' => [
                            'payrollComponentable',
                            'type',
                            'value'
                        ],
                        'operator' => '=',
                        'value' => Compensation::STATUTORY_BENEFIT,
                    ],
                ]
            ],
            [
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
                        'order' => 1,
                        'property' => 'aggregation',
                        'operator' => '=',
                        'value' => true,
                    ],
                    [
                        'order' => 2,
                        'property' => 'formulable_type',
                        'operator' => '=',
                        'value' => Formulable::TAXABLE_INCOME,
                    ],
                ]
            ],
            [
                'order' => 5,
                'key' => 'nontaxable_income',
                'name' => 'Nontaxable income',
                'formulable_type' => Formulable::NONTAXABLE_INCOME,
                'statement_level' => true,
                'aggregation' => true,
                'property' => 'company',
                'attribute' => 'formulas',
                'conditions' => [
                    [
                        'order' => 1,
                        'property' => 'aggregation',
                        'operator' => '=',
                        'value' => true,
                    ],
                    [
                        'order' => 2,
                        'property' => 'formulable_type',
                        'operator' => '=',
                        'value' => Formulable::NONTAXABLE_INCOME,
                    ],
                ]
            ],
            [
                'order' => 6,
                'key' => 'income_taxes',
                'name' => 'Income taxes',
                'formulable_type' => Formulable::INCOME_TAX,
                'statement_level' => true,
                'aggregation' => false,
                'property' => 'employee',
                'attribute' => 'incomeTaxes',
                'conditions' => [
                    [
                        'order' => 1,
                        'property' => 'payroll_componentable_type',
                        'operator' => '=',
                        'value' => 'income_tax',
                    ],
                    [
                        'order' => 2,
                        'property' => 'formulable_type',
                        'operator' => '=',
                        'value' => Formulable::INCOME_TAX,
                    ],
                ]
            ],
            [
                'order' => 7,
                'key' => 'nonstatutory_deductions',
                'name' => 'Nonstatutory deductions',
                'formulable_type' => Formulable::DEDUCTIONS,
                'statement_level' => true,
                'aggregation' => false,
                'property' => 'employee',
                'attribute' => 'deductions',
                'conditions' => [
                    [
                        'order' => 1,
                        'property' => 'payroll_componentable_type',
                        'operator' => '=',
                        'value' => 'deduction',
                    ],
                    [
                        'order' => 2,
                        'property' => 'formulable_type',
                        'operator' => '=',
                        'value' => Formulable::DEDUCTIONS,
                    ],
                    [
                        'order' => 3,
                        'property' => [
                            'payrollComponentable',
                            'type',
                            'value'
                        ],
                        'operator' => '=',
                        'value' => Deduction::DEDUCTION,
                    ],
                ]
            ],
            [
                'order' => 8,
                'key' => 'manual_nonstatutory_deductions',
                'name' => 'Manual nonstatutory deductions',
                'formulable_type' => Formulable::DEDUCTIONS,
                'statement_level' => true,
                'aggregation' => false,
                'property' => 'company',
                'attribute' => 'formulas',
                'conditions' => [
                    [
                        'order' => 1,
                        'property' => 'aggregation',
                        'operator' => '=',
                        'value' => false,
                    ],
                    [
                        'order' => 2,
                        'property' => 'formulable_type',
                        'operator' => '=',
                        'value' => Formulable::DEDUCTIONS,
                    ],
                    [
                        'order' => 3,
                        'property' => 'component_type',
                        'operator' => '=',
                        'value' => Deduction::MANUAL_DEDUCTION,
                    ],
                ]
            ],
            [
                'order' => 9,
                'key' => 'net_income',
                'name' => 'Net income',
                'formulable_type' => Formulable::NET_INCOME,
                'statement_level' => true,
                'aggregation' => true,
                'property' => 'company',
                'attribute' => 'formulas',
                'conditions' => [
                    [
                        'order' => 1,
                        'property' => 'aggregation',
                        'operator' => '=',
                        'value' => true,
                    ],
                    [
                        'order' => 2,
                        'property' => 'formulable_type',
                        'operator' => '=',
                        'value' => Formulable::NET_INCOME,
                    ],
                ]
            ],
        ];
    }
}
